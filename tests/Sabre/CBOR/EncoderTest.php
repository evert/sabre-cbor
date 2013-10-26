<?php

namespace Sabre\CBOR;

class EncoderTest extends \PHPUnit_Framework_TestCase {

    /**
     * @dataProvider data
     */
    function testEncoding($input, $output) {

        $this->assertEquals(
            $output,
            encode($input)
        );

    }

    function data() {

        $r = [];

        // Examples directly taken from rfc7049
        $r[] = [0,    "\x00"];
        $r[] = [1,    "\x01"];
        $r[] = [10,   "\x0a"];
        $r[] = [23,   "\x17"];
        $r[] = [24,   "\x18\x18"];
        $r[] = [100,  "\x18\x64"];
        $r[] = [1000, "\x19\x03\xe8"];

        $r[] = [1000000, "\x1a\x00\x0f\x42\x40"];
        $r[] = [1000000000000, "\x1b\x00\x00\x00\xe8\xd4\xa5\x10\x00"];

        // Not tested, because this is higher than PHP's maximum int.
        // $r[] = [18446744073709551615, "\x1b\xff\xff\xff\xff\xff\xff\xff\xff"];
        // $r[] = [18446744073709551616, "\xc2\x49\x01\x00\x00\x00\x00\x00\x00\x00\x00"];

        // These are lower than PHP's min int.
        // $r[] = [-18446744073709551616, "\x3b\xff\xff\xff\xff\xff\xff\xff\xff"];
        // $r[] = [-18446744073709551617, "\xc3\x49\x01\x00\x00\x00\x00\x00\x00\x00\x00"];

        $r[] = [-1,   "\x20"];
        $r[] = [-10,   "\x29"];
        $r[] = [-100,  "\x38\x63"];
        $r[] = [-1000, "\x39\x03\xe7"];

        // Floating points are not supported yet.
        // $r[] = [0.0,  "\xf9\x00\x00"];
        // $r[] = [-0.0, "\xf9\x80\x00"];
        // $r[] = [1.0,  "\xf9\x3c\x00"];
        // $r[] = [1.1,  "\xfb\x3f\xf1\x99\x99\x99\x99\x99\x9a"];
        // $r[] = [1.5,  "\xf9\x3e\x00"];
        // $r[] = [65504.0,  "\xf9\x7b\xff"];
        // $r[] = [100000.0,  "\xfa\x47\xc3\x50\x00"];
        // $r[] = [3.4028234663852886e+38,  "\xfa\x7f\x7f\xff\xff"];
        // $r[] = [1.0e+300,  "\xfb\x7e\x37\xe4\x3c\x88\x00\x75\x9c"];
        // $r[] = [5.960464477539063e-8,  "\xf9\x00\x01"];
        // $r[] = [0.00006103515625,  "\xf9\x04\x00"];
        // $r[] = [-4.0, "\xf9\xc4\x00"];
        // $r[] = [-4.1, "\xfb\xc0\x10\x66\x66\x66\x66\x66\x66"];
        // $r[] = [INF,  "\xf9\x7c\x00"];
        // $r[] = [NAN , "\xf9\x7e\x00"];
        // $r[] = [-INF , "\xf9\xfc\x00"];

        // Simple values
        $r[] = [false, "\xf4"];
        $r[] = [true,  "\xf5"];
        $r[] = [null,  "\xf6"];

        // PHP has no undefined value
        // $r[] = [undefined,  "\xf7"];

        // This library does not provide a mapping to these simple values.
        // $r[] = [simple(16),  "\xf0"];
        // $r[] = [simple(24),  "\xf8\x18"];
        // $r[] = [simple(255),  "\xf8\xff"];

        // No support for these yet
        // $r[] = [0("2013-03-21T20:04:00Z"), "\xc0\x74\x32\x30\x31\x33\x2d\x30\x33\x2d\x32\x31\x54\x32\x30\x3a"];
        // $r[] = [1(1363896240), "\xc1\x1a\x51\x4b\x67\xb0"];
        // $r[] = [1(1363896240.5), "\xc1\xfb\x41\xd4\x52\xd9\xec\x20\x00\x00"];
        // $r[] = [23(h'01020304'), "\xd7\x44\x01\x02\x03\x04"];
        // $r[] = [24(h'6449455446'), "\xd8\x18\x45\x64\x49\x45\x54\x46"];
        // $r[] = [32(h'http://www.example.com'), "\xd8\x20\x76\x68\x74\x74\x70\x3a\x2f\x2f\x77\x77\x77\x2e\x65\x78\x61\x6d\x70\x6c\x65\x2e\x63\x6f\x6d"];

        // No support for byte string yet.
        // $r[] = ['', "\x40"];
        // $r[] = ['01020304', "\x44\x01\x02\x03\x04"];

        $r[] = ["", "\x60"];
        $r[] = ["a", "\x61\x61"];
        $r[] = ["IETF", "\x64\x49\x45\x54\x46"];
        $r[] = ["\"\\", "\x62\x22\x5c"];

        // The original string from the rfc doc was '\u00fc'.
        $r[] = ["ü", "\x62\xc3\xbc"];

        // The original string from the rfc doc was '\u6c34'.
        $r[] = ["水", "\x63\xe6\xb0\xb4"];

        // The original string from the rfc doc was '\ud800\udd51'.
        $r[] = ["\xf0\x90\x85\x91", "\x64\xf0\x90\x85\x91"];

        // Arrays
        $r[] = [[], "\x80"];
        $r[] = [[1, 2, 3], "\x83\x01\x02\x03"];
        $r[] = [[1, [2, 3], [4, 5]], "\x83\x01\x82\x02\x03\x82\x04\x05"];
        $r[] = [
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25],
            "\x98\x19\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0a\x0b\x0c\x0d\x0e\x0f\x10\x11\x12\x13\x14\x15\x16\x17\x18\x18\x18\x19"
        ];

        // Objects
        $r[] = [ (object)[], "\xa0"];
        return $r;

    }

}
