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
    // removing applescript quirks
    $str = str_replace('"}', '', $str);
    $str = str_replace('{', '', $str);
    $str = str_replace('},', '', $str);
    $str = str_replace('}}', '', $str);
    // remove trailing spaces and comma
    $str = rtrim($str);
    $str = rtrim($str, ',');
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
    
        if (isset($notes[0]))
            $todo['name'] = clean_parsed_strings($notes[0]);
    
        if (isset($notes[1]))
            $todo['notes'] = clean_parsed_strings($notes[1]);

        array_push($elements, $todo);
    }

    return $elements;
}

// execute the script and return 
function run_get_todos()
{
    // applescript: get all todos data
    // location: /prototype/scripts/get_todos.scpt
    $c = [];
    $c[] = 'tell application "Things"';
    $c[] = 'set todos to {}';
    $c[] = 'repeat with todo in to dos';
    $c[] = 'copy {{name:name of todo}, {notes: notes of todo}} to the end of todos';
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

$todos = run_get_todos();
?>

<html>
    <head>
        <title>Things Web App Demo</title>
    </head>
    <body>
        <h1>Things App Tasks!</h1>
        <table>
            <thead>
                <tr>
                    <th>Task Name</th>
                </tr>                
            </thead>
            <tbody>
                <?php foreach ($todos as $todo): ?>
                <tr>
                    <td>
                        <?php echo $todo['name']; ?>
                    </td>
                </tr>
                <?php endforeach; ?>                
            </tbody>
        </table>
    </body>
</html>