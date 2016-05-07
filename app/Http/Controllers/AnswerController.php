<?php

namespace App\Http\Controllers;

use App\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

use App\Http\Requests;

class AnswerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $answers = DB::table('answers')->whereBetween('created_at',
            array($request->get("start_date") != null ? $request->get("start_date") : '1970-1-1 00:00:00',
                $request->get("end_date") != null ? $request->get("end_date") : '2999-1-1 00:00:00'));

        $question_id = intval($request->get("question_id"));
        if ($question_id){
            $answers = $answers->where('question_id', $question_id);
        }

        $poll_id = intval($request->get("poll_id"));
        if ($poll_id){
            $answers = $answers->where('poll_id', $poll_id);
        }

        $questions = Question::all();

        $answers = $answers->get();
        foreach ($answers as $value){
            $value->question = $questions->where('id', intval($value->question_id))->first();
            unset($value->question_id);
        }

        return $answers;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'text' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->all(), 400);
        }

        $question = new Question;
        $question->text = $request->text;
        $question->save();

        return ["message" => "Question saved"];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'text' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->all(), 400);
        }

        $question = Question::find($id);

        if ($question == null){
            return response()->json(["message" => "Question with given id does not exist"], 400);
        }

        $question->text = $request->text;
        $question->save();

        return ["message" => "Question updated"];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (Question::destroy($id) == false){
            return response()->json(["message" => "Question with given id does not exist"], 400);
        }

        return ["message" => "Question deleted"];
    }
}
