<?php

return array(
	//'log' => true,
	'report' => true,
	'log' => function ($e) {
        $data = array(
            'date'        => date("Y-m-d H:i:s"),
            'message'    => $e->getMessage(),
            'trace'    => $e->getTrace(),
            'line'        => $e->getLine(),
            'file'        => $e->getFile()
        );
        $write = file_put_contents(STORAGE . 'errors.log', json_encode($data) . "\n", FILE_APPEND | LOCK_EX);
        echo '<pre>';
        echo $e->getMessage() . "\n";
        echo 'The error has been logged.';
        echo '</pre>';
    }
);
