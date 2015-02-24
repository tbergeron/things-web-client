<?php

// convert array to osascript exec-utable string
function prepare_script($lines)
{
    $first = true;
    foreach ($lines as $line) {
        if ($first) {
            $script = "osascript -ss -e '" . $line . PHP_EOL;
            $first = false;
        } else {
            $script .= $line . PHP_EOL;
        }
    }
    return $script . "'";
}

function clean_parsed_strings($str)
{
    $str = str_replace('"}', '', $str);
    $str = str_replace('},', '', $str);
    $str = str_replace('}}', '', $str);
    return $str;
}

// parse down "recompilable source form" applescript response to array
function parse_todos_response($response)
{
    $elements = [];
    $todos = explode('{{name:"', $response);

    foreach ($todos as $todo) {
        // todo: extract other todo attributes
        $notes = explode('{notes:"', $todo);

        $todo = [];
        $todo['name'] = clean_parsed_strings($notes[0]);
        $todo['notes'] = clean_parsed_strings($notes[1]);

        array_push($elements, $todo);
    }

    return $elements;
}

// execute the script and return 
function run_get_todos()
{
    // applescript: get all todos data
    $c = [];
    $c[] = 'tell application "Things"';
    $c[] = 'set todos to {}';
    $c[] = 'repeat with todo in to dos';
    $c[] = 'copy {{name:name of todo}, {notes: notes of todo}} to the end of todos';
    // $c[] = 'copy {name:name of todo, notes:notes of todo, due_date:due date of todo} to the end of todos';
    $c[] = 'end repeat';
    $c[] = 'return todos';
    $c[] = 'end tell';

    $prepared_script = prepare_script($c);

    ob_start();
    passthru($prepared_script);
    $response = ob_get_contents();
    ob_end_clean();

    return parse_todos_response($response);
}

die(print_r(run_get_todos()));