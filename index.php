<?php
require 'vendor/autoload.php';

use Aws\Exception\AwsException;
use Aws\Polly\PollyClient;

$row = 1;
$tsv = "c1.tsv";


$awsAccessKeyId = 'AKIA6RNMXATYJ4WTAO5P'; //'AKIAJ5NA5QAFGP7THXNA'; //'AKIAJVD6EZ5YLKNERTUA';
$awsSecretKey   = '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'; //'3U1sNGSk4GtiGZMzjIFg4n8QXf8TE7j3Wr+VfULb'; //'67iSdgHxKzTmy+e2QnqiXkKME8q6KvDPHAOyFTmx';
$credentials    = new \Aws\Credentials\Credentials($awsAccessKeyId, $awsSecretKey);

$client         = new \Aws\Polly\PollyClient([
    'version'     => '2016-06-10',
    'credentials' => $credentials,
    'region'      => 'us-east-1',
]);

if (($handle = fopen($tsv, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 10000, "\t")) !== FALSE) {
        $num = count($data);
        echo "<p> $num fields in line $row: <br /></p>\n";
        echo $data;
        $row++;

        if ($data[1] == "Ana") {
            $data[1] = "Joanna";
        } else if ($data[1] == "Eva") {
            $data[1] = "Ivy";
        } else if ($data[1] == "John") {
            $data[1] = "Kevin";
        }

        if ($data[7] == "Ana") {
            $data[7] = "Joanna";
        } else if ($data[7] == "Eva") {
            $data[7] = "Ivy";
        } else if ($data[7] == "John") {
            $data[7] = "Kevin";
        }

        for ($z = 0; $z < 2; $z++) {
            $folder = "Audio_Exportado";

            if ($z == 0) {
                $name = $data[19];
                $expression = $data[0];
                $voice = $data[1];
            } else if ($z == 1) {
                $name = $data[20];
                $expression = $data[6];
                $voice = $data[7];
            }

            if ($folder != Null and $name != Null and $expression != Null) {
                if (!is_dir($folder)) {
                    mkdir($folder);
                }
                $filename = htmlspecialchars($folder) . '/' . $name;
            } else {
                if ($name == Null) {
                    echo "falta a variavel name";
                }
                if ($expression == Null) {
                    echo "falta a variavel expression";
                }
                return;
            }

            $result         = $client->synthesizeSpeech([
                'Engine' => 'neural',
                'OutputFormat' => 'mp3',
                'Text'         => $expression,
                'TextType'     => 'text',
                'VoiceId'      => $voice,
            ]);
            try {
                $resultData     = $result->get('AudioStream')->getContents();
            } catch (AwsException $e) {
                // output error message if fails
                echo $e->getMessage();
                echo "\n";
            }


            $myfile = fopen($filename, "w") or die("Unable to open file!");
            fwrite($myfile, $resultData);
            fclose($myfile);
        }
    }
    fclose($handle);
}
