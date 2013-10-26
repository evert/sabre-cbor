<?php

use Sabre\CBOR;


function encode($value) {

    $stream = fopen('php://memory','r+');
    $encoder = new Sabre\CBOR\Encoder($stream);
    $encoder->encode($value);
    
    rewind($stream);
    return stream_get_contents($stream);

}
