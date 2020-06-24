<?php

function proccessData($filePath) {
    if($filePath == '' && !file_exists($filePath)){
        return 'File not found!';
    }

    $file_parts = pathinfo($filePath);
    $siblings = 0;
    $lineCount = 0;

    switch($file_parts['extension'])
    {
        case 'csv':
            $flag = true;
            
            $handle = fopen($filePath, "r");
            while(($data = fgetcsv($handle)) !== FALSE) {
                if($flag){ $flag = false; continue; }
                $siblings += $data[2];
                $favFood[]  = $data[3];

                $timezone = $data[4];
                $strTz = explode(':', $timezone);
                $a = (str_replace(['+', '-'], '', $strTz[0]) * 60 * 60);
                $b = $strTz[1] * 60;
                switch($timezone[0]) {
                    case '+':
                        $timezone = $data[5] + ($a + $b);
                        break;

                    case '-':
                        $timezone = $data[5] - ($a + $b);
                        break;
                }
                $birthMonth[] = date("F", $data[5]);            
                ++$lineCount;
            }
            break;

        case 'json':
            $fileData = json_decode(file_get_contents($filePath));

            foreach($fileData as $data){
                $siblings += $data->siblings;
                $favFood[] = $data->favourite_food;

                $timezone = $data->birth_timezone;
                $strTz = explode(':', $timezone);
                $a = (str_replace(['+', '-'], '', $strTz[0]) * 60 * 60);
                $b = $strTz[1] * 60;
                switch($timezone[0]) {
                    case '+':
                        $birthtime = ($data->birth_timestamp + ($a + $b));
                        break;

                    case '-':
                        $birthtime = ($data->birth_timestamp - ($a + $b));
                        break;
                }
                $birthMonth[] = date("F", $birthtime);
                ++$lineCount;
            }
            break;
    }
    
 echo "Average siblings: ".round($siblings/$lineCount)." \n\r";  

    $favFood = array_count_values($favFood);
    arsort($favFood);
    $favFood = array_slice($favFood, 0, 3);
    echo "Favourite foods:\n";
    foreach($favFood as $food => $val){
        echo '- '. ucfirst($food)." \t".$val."\n";
    }

    echo "\rBirths per Month:\n";
    $birthMonth = array_count_values($birthMonth);
    uksort($birthMonth , function($a, $b){
        $a = strtotime($a);
        $b = strtotime($b);
        return $a - $b;
    });

    foreach($birthMonth as $month => $count){
        echo '- '. $month ."   \t". $count."\n";
    }
}

proccessData($argv[1]);

?>