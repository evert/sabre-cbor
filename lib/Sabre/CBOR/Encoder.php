<?php

namespace Sabre\CBOR;

class Encoder {

    /**
     * Output stream
     *
     * @var resource
     */
    protected $out;

    /**
     * Creates the encoder.
     *
     * You must supply a stream. The stream will be used to write the CBOR
     * data.
     *
     * @param resource $out
     */
    public function __construct($stream) {

        $this->out = $stream;

    }

    public function encode($val) {

        if ($val instanceof Closure) {
            $val = $val();
        }

        if (is_int($val)) {
            $this->encodeInt($val);

        } elseif (is_string($val)) {
            $this->encodeString($val);

        } elseif (is_array($val)) {
            $this->encodeArray($val);

        } elseif (is_object($val)) {
            $this->encodeObject($val);

        } elseif (is_bool($val)) {
            $this->encodeBool($val);

        } elseif (is_null($val)) {
            $this->encodeNull();

        } elseif (is_float($val)) {
            throw new \InvalidArgumentException('floats are not yet supported :(');
        }

    }

    /**
     * Encodes an integer.
     */
    public function encodeInt($val) {

        // Positive
        if ($val >= 0) {
            $this->writeDataValue(TYPE_UNSIGNED_INT, $val);
        } else {
            // negative
            $this->writeDataValue(TYPE_NEGATIVE_INT, -1 - $val);
        } 

    }

    /**
     * Encoding a string. 
     * 
     * @param string $val 
     * @return void
     */
    public function encodeString($val) {

        // The assumption is that every string is encoded as UTF-8.
        $this->writeDataValue(TYPE_TEXT_STRING, strlen($val));
        $this->writeString($val);

    }

    /**
     * Encodes a PHP array, which may either be represented by a CBOR array or 
     * a map. 
     * 
     * @param array $val 
     * @return void
     */
    public function encodeArray(array $val) {

        // Just like PHP's json encoder, if the keys of the array are not a 
        // continuous numeric sequence starting from 0, we'll encode it as an 
        // object instead.
        $index = 0;
        foreach($val as $key => $discard) {
            if ($key !== $index) {
                $this->encodeObject((object)$val);
                return;
            }
            $index++;
        }

        $this->writeDataValue(TYPE_ARRAY, count($val));
        foreach($val as $subValue) {
            $this->encode($subValue);
        }

    }

    /**
     * Encodes a plain PHP object, which is encoded as a CBOR map. 
     *
     * @param object $val 
     * @return void
     */
    public function encodeObject($val) {

        $val = get_object_vars($val);

        $this->writeDataValue(TYPE_MAP, count($val));
        foreach($val as $key=>$subValue) {
            $this->encode($key);
            $this->encode($subValue);
        }

    }

    /**
     * Writes a boolean. 
     * 
     * @param bool $val 
     * @return void
     */
    public function encodeBool($val) {

        if ($val) {
            $this->writeDataValue(TYPE_MISC, 21);
        } else {
            $this->writeDataValue(TYPE_MISC, 20);
        }

    }

    /**
     * Writes a null. 
     * 
     * @return void
     */
    public function encodeNull() {

        $this->writeDataValue(TYPE_MISC, 22);

    }


    /**
     * Writes a value to the output stream. This can be an unsigned int, a 
     * negative int, or an indicator of the length of an array or string.
     *
     * @param int $majorType
     * @param int $val
     */
    protected function writeDataValue($majorType, $val) {

        /**
         * Encoding integers that don't fit into 32 bits as 64 bits, but we
         * need to make sure that we're running a PHP version that supports
         * 64bit integers, otherwise I have no idea what will happen here.
         */
        if (PHP_INT_SIZE >= 8 && $val >= 4294967296) {
            // 27 denotes encoding the integer as 64 bits (8 bytes).
            $this->writeByte($majorType | 27);
            
            // pack() does not support 64 bits, so we're splitting this in two 32 
            // bit numbers.
            $top = $val >> 32;
            $bottom = $val & 0xffffffff;
            $this->writeString(pack("NN", $top, $bottom));

        // Is it larger than a 16 bit unsigned int? then we'll store it in 4 
        // bytes (32 bits)
        } elseif ($val >= 65535) {
            $this->writeByte($majorType | 26);
            $this->writeString(pack("N", $val));

        // 16 bit encoding
        } elseif ($val > 255) {
            $this->writeByte($majorType | 25);
            $this->writeString(pack("n", $val));

        // 8 bit encoding
        } elseif ($val > 23) {
            $this->writeByte($majorType | 24);
            $this->writeByte($val);
                
        // We use the last 5 bits to encode the value
        } else {
            $this->writeByte($majorType | $val);
        } 

    }

    /**
     * Writes a single byte to the output stream.
     *
     * The byte is represented as a php integer. 
     * 
     * @param int $byte 
     * @return void
     */
    protected function writeByte($byte) {

        fwrite($this->out, chr($byte));

    }

    /**
     * Writes a raw string directly to the wire 
     * 
     * @param string $str 
     * @return void
     */
    protected function writeString($str) {

        fwrite($this->out, $str);

    }

}
