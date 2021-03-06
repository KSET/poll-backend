<?php

namespace App\Http\Controllers;

use App\Answer;
use App\Poll;
use App\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;
use DateInterval;
use Carbon\Carbon;

use App\Http\Requests;

class PollsController extends Controller
{

    public function index(Request $request)
    {
        $polls = DB::table('polls')->whereBetween('created_at',
            array($request->get("start_date") != null ? $request->get("start_date") : '1970-1-1 00:00:00',
                $request->get("end_date") != null ? $request->get("end_date") : '2999-1-1 00:00:00'))->get();

        $answers = Answer::all();

        foreach ($polls as $poll){
            $poll->answers = array_slice($answers->where('poll_id', $poll->id)->toArray(), 0);
        }

        return $polls;
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

        foreach (Question::all() as $question){
            if(!array_key_exists($question->id, $request->answers)){
                return response()->json(["message" => "Answer for question with id {$question->id} not given"], 400);
            }
        }
        
        $poll->save();

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

    public function getByScheduel(){

        $schedule = [
            "CS Computer Systems" => "2016-5-9 09:05:00",
            "Montelektro" => "2016-5-9 09:35:00",
            "Rimac" => "2016-5-9 10:05:00",
            "Rimac #2" => "2016-5-9 10:35:00",
            "CetiTec" => "2016-5-9 11:05:00",
            "Mikroprojekt" => "2016-5-9 11:35:00",
            "Farmeron" => "2016-5-9 12:05:00",
            "Five" => "2016-5-9 12:35:00",
            "AdCumulus / Click Attack" => "2016-5-9 13:05:00",
            "Infinum" => "2016-5-9 13:35:00",
            "GDI Gisdata" => "2016-5-9 14:05:00",
            "Microblink" => "2016-5-9 14:35:00",
            "Vipnet" => "2016-5-9 15:05:00",
            "Infobip" => "2016-5-9 15:35:00",
            "Intesa SanPaolo Card" => "2016-5-9 16:05:00",
            "Megatrend" => "2016-5-9 16:35:00",
            "Microsoft" => "2016-5-9 17:05:00",
            "Microsoft #2" => "2016-5-9 17:35:00",
            "Verso / Altima" => "2016-5-9 18:05:00",
            
            "Infigo" => "2016-5-10 9:05:00",
            "REC" => "2016-5-10 9:35:00",
            "Google" => "2016-5-10 10:05:00",
            "Google #2" => "2016-5-10 10:35:00",
            "Poslovna inteligencija" => "2016-5-10 11:05:00",
            "Cloudsense" => "2016-5-10 11:35:00",
            "AVL-AST" => "2016-5-10 12:05:00",
            "Ericsson" => "2016-5-10 12:35:00",
            "Croteam" => "2016-5-10 13:05:00",
            "Nanobit" => "2016-5-10 13:35:00",
            "T-HT / Combis / Iskon" => "2016-5-10 14:05:00",
            "Jane Street" => "2016-5-10 14:35:00",
            "SedamIT" => "2016-5-10 15:05:00",
            "Acceleratio" => "2016-5-10 15:35:00",
            "Xylon" => "2016-5-10 16:05:00",
            "IN2" => "2016-5-10 16:35:00",
            "Panel discussion" => "2016-5-10 17:05:00",
            "Panel discussion #2" => "2016-5-10 17:35:00",
            "Degordian" => "2016-5-10 18:05:00",
        ];
        
        $returnJson = null;
        
        foreach($schedule as $key => $value){
            $end_val = Carbon::createFromFormat('Y-m-d H:i:s', $value);
            $end_val->addMinutes(30);
            
             $answers = DB::select(
                DB::raw('SELECT AVG(score) as avg_score, question_id, text as question_text
                    FROM answers JOIN questions ON
                        answers.question_id = questions.id
                    WHERE answers.created_at >= "' .$value. '" AND answers.created_at < "' .$end_val->format('Y-m-d H:i:s'). '"
                    GROUP BY question_id, text'));
        
            $retVal = [
                "start" => $value,
                "end" => $end_val->format('Y-m-d H:i:s'),
                "answers" => $answers
                ];
            
            $returnJson[$key] = $retVal;
        }

        return $returnJson;
    }
}
