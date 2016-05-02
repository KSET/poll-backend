<?php

namespace App\Http\Controllers;

use App\Answer;
use App\Poll;
use App\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

use App\Http\Requests;

class PollsController extends Controller
{

    public function index()
    {
        return Poll::all()->toJson();
    }

    public function getFromPeriod(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start' => 'required|date',
            'end' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->all(), 400);
        }

        return DB::table('polls')->whereBetween('created_at', array($request->start, $request->end))->get();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'answers' => 'required|array'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->all(), 400);
        }

        $poll = new Poll;
        $poll->save();

        foreach (Question::all() as $question){
            if(!array_key_exists($question->id, $request->answers)){
                return response()->json(["message" => "Answer for question with id {$question->id} not given"], 400);
            }
        }

        foreach (Question::all() as $question){
            $score = $request->answers[$question->id];

            $answer = new Answer;
            $answer->question_id = $question->id;
            $answer->poll_id = $poll->id;
            $answer->score = $score;
            $answer->save();
        }


        return ["message" => "Question saved"];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (Poll::destroy($id) == false){
            return response()->json(["message" => "Poll with given id does not exist"], 400);
        }

        return ["message" => "Poll deleted"];
    }
}
