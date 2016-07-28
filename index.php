<?php
/**
 * Author: John
 * Date: 7/27/2016
 */
/**
 * @param array $player1 The server
 * @param array $player2 The servee
 * @return bool true if server wins, false is server looses volley
 */
function serve(array $player1, array $player2) {
    $ball_possessor = $player1;
    echo $player1['name'], ' serves the ball.';
    if(!(rand(1,100)<=$player1['serve_accuracy'])) {
        echo "\r\nThe serve is out of bounds.";
        $server_wins = false;
    }
    echo "...in bounds.\r\n";
    while (!isset($server_wins)) {
        $ball_possessor = ($ball_possessor == $player1) ? $player2 : $player1;
        echo $ball_possessor['name'], ' swings at the ball.';
        if(!(rand(1,100)<=$ball_possessor['return_skill'])) {
            echo ".....and misses the ball.\r\n";
            $server_wins = ($ball_possessor == $player1) ? false : true;
        }
        else if(!(rand(1,100)<=$ball_possessor['return_accuracy'])) {
            echo ".....and hits it out of bounds.\r\n";
            $server_wins = ($ball_possessor == $player1) ? false : true;
        } else
            echo "...and returns successfully.\r\n";
    };
    return $server_wins;
}

/**
 * @param array $player1
 * @param array $player2
 * @return array $player
 */
function play(array $player1, array $player2) {

    if($player1['name'] == 'nobody' || $player2['name'] == 'nobody')
        return false;
    $player1['score'] = $player2['score'] = 0;

    echo '<textarea class="form-control" rows="10">';
    $i = 1;
    while($player1['score'] < 11 && $player2['score'] < 11) {
        if ($i <= 2) {
            echo "$player1[name] will serve.\r\n";
            $outcome = serve($player1, $player2);
            if($outcome) {
                echo $player1['name'], " scored.\r\n";
                $player1['score']++;
                echo "The score is now $player1[name]: $player1[score]   $player2[name]: $player2[score]\r\n";
            } else {
                echo $player2['name'], " scored.\r\n";
                $player2['score']++;
                echo "The score is now $player1[name]: $player1[score]   $player2[name]: $player2[score]\r\n";
            }
        } else {
            echo "$player2[name] will serve.\r\n";
            $outcome = serve($player2, $player1);
            if($outcome) {
                echo $player2['name'], " scored.\r\n";
                $player2['score']++;
                echo "The score is now $player1[name]: $player1[score]   $player2[name]: $player2[score]\r\n";
            } else {
                echo $player1['name'], " scored.\r\n";
                $player1['score']++;
                echo "The score is now $player1[name]: $player1[score]   $player2[name]: $player2[score]\r\n";
            }
        }
        ($i == 4) ? $i = 1 : $i++;
    }
    if ($player1['score'] == 11) {
        echo "$player1[name] won the game against $player2[name]";
        echo '</textarea>';
        return $player1;
    } elseif($player2['score'] == 11) {
        echo "$player2[name] won the game against $player1[name]";
        echo '</textarea>';
        return $player2;
    }
}

//Get players
$handle = fopen('data.csv', 'r');
$contents = array();

while(!feof($handle)) {
    $row = fgetcsv($handle);
    if(isset($row))
        $contents[] = $row;
}
$headers = array_shift($contents);
$players = array();
foreach($contents as $row)
    if(is_array($row))
        $players[] = array_combine($headers, $row);

//Output
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset="utf-8">
    <title>Ping Pong Tournament</title>
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons" />
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" />

    <link href="assets/css/bootstrap.min.css" rel="stylesheet" />
    <link href="assets/css/material-kit.css" rel="stylesheet"/>
    <link href="assets/css/styles.css" rel="stylesheet" />
</head>
<body>
    <nav class="navbar navbar-default" role="navigation">
        <div class="container-fluid">
            <div class="navbar-header">
                <h2>Welcome to the ping-pong tournament!</h2>
            </div>
    </nav>
    <h3>Here are the competitors:</h3>
    <table class="table">
        <thead>
            <tr>
                <?php
                foreach($headers as $header)
                    echo '<th class="text-center"><span class="label label-warning">',$header,'</span></th>';
                ?>
            </tr>
        </thead>
        <tbody>
        <?php
        foreach($players as $player) {
            echo '<tr>';
            foreach($player as $key => $stat)
                if($key == 'name')
                    echo '<td class="text-center"><i class="material-icons">face</i><span>',$stat,'</span></td>';
                else
                    echo '<td class="text-center"><span>',$stat,'</span></td>';
            echo '</tr>';
        }
        ?>
        </tbody>
    </table>
    <?php
    if(count($players) % 2 != 0)
        array_push($players, array('name' => 'nobody'));
    $count = count($players);

    for($i = 0; $i < $count; $i++) {
        $players[$i]['wins'] = 0;
        $players[$i]['losses'] = 0;
    }
    //Determine match-ups and play games
    for ($i = 0; $i < $count -1; $i++) {
        echo '<h4>Round ', $i + 1, '</h4>';
        ?>
        <div class="progress">
            <div class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 100%;"></div>
        </div>
        <?php
        //One half of the array plays the other half, i.e player[n] plays player[n + (length / 2]
        for ($j = 0; $j < $count / 2; $j++) {
            $player1 = $players[$j];
            $player2 = $players[$j + ($count / 2)];
            echo '<h5>', $player1['name'], ' vs. ', $player2['name'], '</h5>';
            $winner = play($player1, $player2);
            if (isset($winner) && $winner != false) {
                if ($winner['name'] == $player1['name']) {
                    $players[$j]['wins']++;
                    $players[$j + ($count / 2)]['losses']++;
                } else {
                    $players[$j + ($count / 2)]['wins']++;
                    $players[$j]['losses']++;
                }
            }
        }
        //Rotate all array members by one, expect for index zero
        $temp = $players[$count - 1];
        for($k = $count - 1; $k > 1 ; $k--)
            $players[$k] = $players[$k - 1];
        $players[1] = $temp;
    }

    //Figure out who won
    $scores = array();
    foreach ($players as $key => $player) {
        if ($player['name'] != 'nobody') {
            echo '<p>', $player['name'], ' has ', $player['wins'], ' wins and ', $player['losses'],' losses.</p>';
            $scores[$player['name']] = $player['wins'];
        }
    }
    $max = max($scores);
    $winners = array_keys($scores, $max);
    $verb = (count($winners) == 1) ? ' wins the tournament!' : ' tie for first place.';
    echo '<h5><i class="material-icons">star</i>', implode(' and ', $winners), $verb, '<i class="material-icons">star</i></h5>';
    ?>
</body>