<?php

namespace App\Http\Controllers;

use App\Answer;
use Illuminate\Http\Request;
use Validator;

use App\Http\Requests;

class AnswerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Answer::all()->toJson();
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
