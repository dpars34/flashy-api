<?php

return [
  'credentials' => realpath('.' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . env('FIREBASE_CREDENTIALS')),
];