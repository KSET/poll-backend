<?php

Route::resource('questions', 'QuestionsController');
Route::resource('answers', 'AnswerController');

Route::post('polls_period', 'PollsController@getFromPeriod');
Route::resource('polls', 'PollsController');

Route::get('schedule', 'PollsController@getByScheduel')
