<?php

$fp = fopen("/root/commits.txt", "r");

$commits = array();
$previous_author = '';
while (($line = fgets($fp)) !== FALSE) {
  list($mode, $sha, $current_author, $subject) = explode('|', $line);
  if ($previous_author == '' || $previous_author !== $current_author) {
    $previous_author = $current_author;
  }
  else {
    $mode = 'f';
  }
  $output[] = implode(' ', array($mode, $sha, $subject));
}

fclose($fp);

print implode(' ', $output);
