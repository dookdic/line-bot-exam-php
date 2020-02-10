<?php // callback.php

require "vendor/autoload.php";
require_once('vendor/linecorp/line-bot-sdk/line-bot-sdk-tiny/LINEBotTiny.php');

$access_token = 'OW9lbngKJs5NYK9fdHfBRskNGcNfpAgo/ajcfuT/3kQEi+eORymO2i8BnVf+foI5ZNU9Usp5aFyM9JwqPMVLcTXjclnReEJ1eck1TkOz7OWWjtSVIJ/0qeaZLS0Qov4HyOJGHqc4hM1zVrkIVnoRzwdB04t89/1O/w1cDnyilFU=';

// Get POST body content
$content = file_get_contents('php://input');
// Parse JSON
$events = json_decode($content, true);
// Validate parsed JSON data
if (!is_null($events['events'])) {
	// Loop through each event
	foreach ($events['events'] as $event) {
		// Reply only when message sent is in 'text' format
		if ($event['type'] == 'message' && $event['message']['type'] == 'text') {
			// Get text sent
			$text = $event['source']['userId'];
			// Get replyToken
			$replyToken = $event['replyToken'];

			// Build message to reply back
			$messages = [
				'type' => 'text',
				'text' => $text
			];

			// Make a POST Request to Messaging API to reply to sender
			$url = 'https://api.line.me/v2/bot/message/reply';
			$data = [
				'replyToken' => $replyToken,
				'messages' => [$messages],
			];
			$post = json_encode($data);
			$headers = array('Content-Type: application/json', 'Authorization: Bearer ' . $access_token);

			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			$result = curl_exec($ch);
			curl_close($ch);

			echo $result . "\r\n";
		}
	}
}
echo "OK";

var ssId = "18zLuQ8vpKROIJfD4V98Poqf68YvL7oElQOqCryaFlnM";
var ss = SpreadsheetApp.openById(ssId);
var sheetProduct = ss.getSheetByName("id");
var sheetLog = ss.getSheetByName("log"); //get sheet for log

function doPost(e) {
  var data = JSON.parse(e.postData.contents); //convert request srting in JSON format into JavaScript object
  
  //Log text message
  var timeStamp = data.originalDetectIntentRequest.payload.data.timestamp;
  var d = new Date(timeStamp); //creates a JS date object form milliseconds
  var formattedDate = d.getDate() + "/" + (d.getMonth() + 1) + "/" + d.getFullYear();
  
  var userId = data.originalDetectIntentRequest.payload.data.source.userId;
  var userText = data.originalDetectIntentRequest.payload.data.message.text;
  var intentName = data.queryResult.intent.displayName;
  
  //set valus to sheet
  var lastRow = sheetLog.getLastRow();
  sheetLog.getRange(lastRow + 1, 1).setValue(formattedDate);
  sheetLog.getRange(lastRow + 1, 2).setValue(userId);
  sheetLog.getRange(lastRow + 1, 3).setValue(userText);
  sheetLog.getRange(lastRow + 1, 4).setValue(intentName);
  
  //get product quantity
  var userMsg = data.originalDetectIntentRequest.payload.data.message.text;
  var values = sheetProduct.getRange(2, 1, sheetProduct.getLastRow(), sheetProduct.getLastColumn()).getValues();
  for (var i = 0; i < values.length; i++) {
    if (values[i][0] == userMsg) {
      i = i + 2;
      var Data = sheetProduct.getRange(i, 2).getValue();
      
      var result = {
        fulfillmentMessages: [
          {
            platform: "line",
            type: 4,
            payload: {
              line: {
                type: "text",
                text: Data
              }
            }
          }
        ]
      };
      
      //response to dialogflow
      var replyJSON = ContentService.createTextOutput(JSON.stringify(result)).setMimeType(ContentService.MimeType.JSON);
      return replyJSON;
    }
  }
}
