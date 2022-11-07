<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\APIResponse;
use App\Models\Loan;
use App\Models\LoanTenure;
use App\Models\User;
use Carbon\Carbon;
use Dflydev\DotAccessData\Data;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LoanController extends Controller
{
    use APIResponse;

    public static function calculate_loan_terms_tenure_dates($loan_amount, $term)
    {
        $each_part_payment = ($loan_amount / $term);
        $tenures = [];

        $last_date_for_part_payment = Carbon::now();
        for ($i = 1; $i <= $term; $i++) {
            $tenures[$i]['part_payment_amount'] = round($each_part_payment, '2');
            $tenures[$i]['due_date'] = $last_date_for_part_payment->addDays(7)->toDateString();
            $tenures[$i]['loan_terms'] = $i;
        }

        return ($tenures);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            if ($request->user()->can('viewAny', Loan::class)) {
                return $this->response(Loan::all(), false, true);
            } else {
                $loans = Loan::with('loan_tenure')->where('user_id', Auth::id())->get();
                return $this->response($loans, false, true, ['All users loans are fetched']);
            }
        } catch (\Throwable $throwable) {
            return $this->response([], true, false, [$throwable->getMessage()]);
        }

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            if ($request->user()->cannot('create', Loan::class)) {
                return $this->response([], true, false, ['you are not authorized for this operation']);
            }

            /*Validating Request*/
            $validate = Validator::make($request->all(),
                [
                    'total_loan_amount' => 'required|numeric',
                    'terms' => 'required|integer|between:1,52',
                ]);

            if ($validate->fails()) {
                return $this->response([], true, false, [$validate->errors()]);
            }

            /* Request valid, moving to save it into loan, creating loan tenures, using terms given by user. */

            $loan = new Loan($request->input());
            $loan->loan_terms = $request->input('terms');
            $loan->user_id = Auth::id();

            if ($loan->save()) {
                $tenures = self::calculate_loan_terms_tenure_dates($loan->total_loan_amount, $loan->loan_terms);
                $loan->loan_tenure()->createMany($tenures);
            }
            return $this->response(['loan' => $loan->withoutRelations(), 'tenure' => $loan->loan_tenure], false, true, ['Your loan has been created successfully']);

        } catch (\Throwable $throwable) {
            return $this->response([], true, false, [$throwable->getMessage()]);
        }


    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function approve_loan(Request $request)
    {
        try {
            /*Validating Request*/
            $validate = Validator::make($request->all(),
                [
                    'loan_id' => 'required|exists:loans,id',
                ]);

            if ($validate->fails()) {
                return $this->response([], true, false, [$validate->errors()]);
            }

            if (Auth::user()->role === 'admin') {
                $loan = Loan::find($request->loan_id);
                $loan->loan_status = 'approved';
                if ($loan->save()) {
                    return $this->response([], false, true, ['Loan Approved Successfully.']);
                }
            } else {
                return $this->response([], false, true, ['You are not authorized for this operation.']);
            }
        } catch (\Throwable $throwable) {
            return $this->response([], true, false, [$throwable->getMessage()]);
        }
    }


    public function repay_loan(Request $request)
    {
        try {
            if ($request->user()->cannot('update', Loan::class)) {
                return $this->response([], true, false, ['you are not authorized for this operation']);
            }


            /*Validating Request*/
            $validate = Validator::make($request->all(),
                [
                    'loan_id' => 'required|exists:loans,id',
                    'amount' => ['required']
                ]);

            if ($validate->fails()) {
                return $this->response([], true, false, [$validate->errors()]);
            }

            // laravel by default doesn't provide anything to check if amount is greater than the one in the database, doing
            // it manually, due to time constraint,

            /* checking loan tenure wrt loan's status and loan id, we can do that directly via loan_tenure id too */
            $loan_tenure_conditions = LoanTenure::where([['part_payment_status', '=', 'pending'], ['loan_id', '=', $request->loan_id]]);

            /*get first pending schedule payment*/
            $loan_tenure = $loan_tenure_conditions->first();
            /*check if amount is enough to pay*/

            if ( $loan_tenure->part_payment_amount <= $request->amount) {
                $loan_tenure->part_payment_status = "paid";
                if ($loan_tenure->save()) {
                    /* checking loans status and marking it as paid (if required) */
                    if ($loan_tenure_conditions->count() === 0) {
                        Loan::where('loan_id', $request->loan_id)->update(['loan_status'=>'paid']);
                    }
                    return $this->response([], false, true, ['Loan tenure paid Successfully.']);
                }
            } else {
                return $this->response([], true, false, ['term amount should be greater than the scheduled payment. ']);
            }
        } catch (\Throwable $throwable) {
            return $this->response([], true, false, [$throwable->getMessage()]);
        }
    }

}
