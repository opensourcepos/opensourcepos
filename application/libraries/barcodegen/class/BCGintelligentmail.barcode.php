<?php
/**
 *--------------------------------------------------------------------
 *
 * Sub-Class - Intelligent Mail
 *
 * A postnet is composed of either 5, 9 or 11 digits used by US postal service.
 *
 *--------------------------------------------------------------------
 * Copyright (C) Jean-Sebastien Goupil
 * http://www.barcodephp.com
 */
include_once('BCGParseException.php');
include_once('BCGArgumentException.php');
include_once('BCGBarcode.php');
include_once('BCGBarcode1D.php');

class BCGintelligentmail extends BCGBarcode1D {
    private $barcodeIdentifier;         // string
    private $serviceTypeIdentifier;     // string
    private $mailerIdentifier;          // string
    private $serialNumber;              // string

    private $quietZone;                 // bool

    private $data;

    private static $characterTable1 = array(
        31,     7936,   47,     7808,   55,     7552,   59,     7040,   61,     6016,
        62,     3968,   79,     7744,   87,     7488,   91,     6976,   93,     5952,
        94,     3904,   103,    7360,   107,    6848,   109,    5824,   110,    3776,
        115,    6592,   117,    5568,   118,    3520,   121,    5056,   122,    3008,
        124,    1984,   143,    7712,   151,    7456,   155,    6944,   157,    5920,
        158,    3872,   167,    7328,   171,    6816,   173,    5792,   174,    3744,
        179,    6560,   181,    5536,   182,    3488,   185,    5024,   186,    2976,
        188,    1952,   199,    7264,   203,    6752,   205,    5728,   206,    3680,
        211,    6496,   213,    5472,   214,    3424,   217,    4960,   218,    2912,
        220,    1888,   227,    6368,   229,    5344,   230,    3296,   233,    4832,
        234,    2784,   236,    1760,   241,    4576,   242,    2528,   244,    1504,
        248,    992,    271,    7696,   279,    7440,   283,    6928,   285,    5904,
        286,    3856,   295,    7312,   299,    6800,   301,    5776,   302,    3728,
        307,    6544,   309,    5520,   310,    3472,   313,    5008,   314,    2960,
        316,    1936,   327,    7248,   331,    6736,   333,    5712,   334,    3664,
        339,    6480,   341,    5456,   342,    3408,   345,    4944,   346,    2896,
        348,    1872,   355,    6352,   357,    5328,   358,    3280,   361,    4816,
        362,    2768,   364,    1744,   369,    4560,   370,    2512,   372,    1488,
        376,    976,    391,    7216,   395,    6704,   397,    5680,   398,    3632,
        403,    6448,   405,    5424,   406,    3376,   409,    4912,   410,    2864,
        412,    1840,   419,    6320,   421,    5296,   422,    3248,   425,    4784,
        426,    2736,   428,    1712,   433,    4528,   434,    2480,   436,    1456,
        440,    944,    451,    6256,   453,    5232,   454,    3184,   457,    4720,
        458,    2672,   460,    1648,   465,    4464,   466,    2416,   468,    1392,
        472,    880,    481,    4336,   482,    2288,   484,    1264,   488,    752,
        527,    7688,   535,    7432,   539,    6920,   541,    5896,   542,    3848,
        551,    7304,   555,    6792,   557,    5768,   558,    3720,   563,    6536,
        565,    5512,   566,    3464,   569,    5000,   570,    2952,   572,    1928,
        583,    7240,   587,    6728,   589,    5704,   590,    3656,   595,    6472,
        597,    5448,   598,    3400,   601,    4936,   602,    2888,   604,    1864,
        611,    6344,   613,    5320,   614,    3272,   617,    4808,   618,    2760,
        620,    1736,   625,    4552,   626,    2504,   628,    1480,   632,    968,
        647,    7208,   651,    6696,   653,    5672,   654,    3624,   659,    6440,
        661,    5416,   662,    3368,   665,    4904,   666,    2856,   668,    1832,
        675,    6312,   677,    5288,   678,    3240,   681,    4776,   682,    2728,
        684,    1704,   689,    4520,   690,    2472,   692,    1448,   696,    936,
        707,    6248,   709,    5224,   710,    3176,   713,    4712,   714,    2664,
        716,    1640,   721,    4456,   722,    2408,   724,    1384,   728,    872,
        737,    4328,   738,    2280,   740,    1256,   775,    7192,   779,    6680,
        781,    5656,   782,    3608,   787,    6424,   789,    5400,   790,    3352,
        793,    4888,   794,    2840,   796,    1816,   803,    6296,   805,    5272,
        806,    3224,   809,    4760,   810,    2712,   812,    1688,   817,    4504,
        818,    2456,   820,    1432,   824,    920,    835,    6232,   837,    5208,
        838,    3160,   841,    4696,   842,    2648,   844,    1624,   849,    4440,
        850,    2392,   852,    1368,   865,    4312,   866,    2264,   868,    1240,
        899,    6200,   901,    5176,   902,    3128,   905,    4664,   906,    2616,
        908,    1592,   913,    4408,   914,    2360,   916,    1336,   929,    4280,
        930,    2232,   932,    1208,   961,    4216,   962,    2168,   964,    1144,
        1039,   7684,   1047,   7428,   1051,   6916,   1053,   5892,   1054,   3844,
        1063,   7300,   1067,   6788,   1069,   5764,   1070,   3716,   1075,   6532,
        1077,   5508,   1078,   3460,   1081,   4996,   1082,   2948,   1084,   1924,
        1095,   7236,   1099,   6724,   1101,   5700,   1102,   3652,   1107,   6468,
        1109,   5444,   1110,   3396,   1113,   4932,   1114,   2884,   1116,   1860,
        1123,   6340,   1125,   5316,   1126,   3268,   1129,   4804,   1130,   2756,
        1132,   1732,   1137,   4548,   1138,   2500,   1140,   1476,   1159,   7204,
        1163,   6692,   1165,   5668,   1166,   3620,   1171,   6436,   1173,   5412,
        1174,   3364,   1177,   4900,   1178,   2852,   1180,   1828,   1187,   6308,
        1189,   5284,   1190,   3236,   1193,   4772,   1194,   2724,   1196,   1700,
        1201,   4516,   1202,   2468,   1204,   1444,   1219,   6244,   1221,   5220,
        1222,   3172,   1225,   4708,   1226,   2660,   1228,   1636,   1233,   4452,
        1234,   2404,   1236,   1380,   1249,   4324,   1250,   2276,   1287,   7188,
        1291,   6676,   1293,   5652,   1294,   3604,   1299,   6420,   1301,   5396,
        1302,   3348,   1305,   4884,   1306,   2836,   1308,   1812,   1315,   6292,
        1317,   5268,   1318,   3220,   1321,   4756,   1322,   2708,   1324,   1684,
        1329,   4500,   1330,   2452,   1332,   1428,   1347,   6228,   1349,   5204,
        1350,   3156,   1353,   4692,   1354,   2644,   1356,   1620,   1361,   4436,
        1362,   2388,   1377,   4308,   1378,   2260,   1411,   6196,   1413,   5172,
        1414,   3124,   1417,   4660,   1418,   2612,   1420,   1588,   1425,   4404,
        1426,   2356,   1441,   4276,   1442,   2228,   1473,   4212,   1474,   2164,
        1543,   7180,   1547,   6668,   1549,   5644,   1550,   3596,   1555,   6412,
        1557,   5388,   1558,   3340,   1561,   4876,   1562,   2828,   1564,   1804,
        1571,   6284,   1573,   5260,   1574,   3212,   1577,   4748,   1578,   2700,
        1580,   1676,   1585,   4492,   1586,   2444,   1603,   6220,   1605,   5196,
        1606,   3148,   1609,   4684,   1610,   2636,   1617,   4428,   1618,   2380,
        1633,   4300,   1634,   2252,   1667,   6188,   1669,   5164,   1670,   3116,
        1673,   4652,   1674,   2604,   1681,   4396,   1682,   2348,   1697,   4268,
        1698,   2220,   1729,   4204,   1730,   2156,   1795,   6172,   1797,   5148,
        1798,   3100,   1801,   4636,   1802,   2588,   1809,   4380,   1810,   2332,
        1825,   4252,   1826,   2204,   1857,   4188,   1858,   2140,   1921,   4156,
        1922,   2108,   2063,   7682,   2071,   7426,   2075,   6914,   2077,   5890,
        2078,   3842,   2087,   7298,   2091,   6786,   2093,   5762,   2094,   3714,
        2099,   6530,   2101,   5506,   2102,   3458,   2105,   4994,   2106,   2946,
        2119,   7234,   2123,   6722,   2125,   5698,   2126,   3650,   2131,   6466,
        2133,   5442,   2134,   3394,   2137,   4930,   2138,   2882,   2147,   6338,
        2149,   5314,   2150,   3266,   2153,   4802,   2154,   2754,   2161,   4546,
        2162,   2498,   2183,   7202,   2187,   6690,   2189,   5666,   2190,   3618,
        2195,   6434,   2197,   5410,   2198,   3362,   2201,   4898,   2202,   2850,
        2211,   6306,   2213,   5282,   2214,   3234,   2217,   4770,   2218,   2722,
        2225,   4514,   2226,   2466,   2243,   6242,   2245,   5218,   2246,   3170,
        2249,   4706,   2250,   2658,   2257,   4450,   2258,   2402,   2273,   4322,
        2311,   7186,   2315,   6674,   2317,   5650,   2318,   3602,   2323,   6418,
        2325,   5394,   2326,   3346,   2329,   4882,   2330,   2834,   2339,   6290,
        2341,   5266,   2342,   3218,   2345,   4754,   2346,   2706,   2353,   4498,
        2354,   2450,   2371,   6226,   2373,   5202,   2374,   3154,   2377,   4690,
        2378,   2642,   2385,   4434,   2401,   4306,   2435,   6194,   2437,   5170,
        2438,   3122,   2441,   4658,   2442,   2610,   2449,   4402,   2465,   4274,
        2497,   4210,   2567,   7178,   2571,   6666,   2573,   5642,   2574,   3594,
        2579,   6410,   2581,   5386,   2582,   3338,   2585,   4874,   2586,   2826,
        2595,   6282,   2597,   5258,   2598,   3210,   2601,   4746,   2602,   2698,
        2609,   4490,   2627,   6218,   2629,   5194,   2630,   3146,   2633,   4682,
        2641,   4426,   2657,   4298,   2691,   6186,   2693,   5162,   2694,   3114,
        2697,   4650,   2705,   4394,   2721,   4266,   2753,   4202,   2819,   6170,
        2821,   5146,   2822,   3098,   2825,   4634,   2833,   4378,   2849,   4250,
        2881,   4186,   2945,   4154,   3079,   7174,   3083,   6662,   3085,   5638,
        3086,   3590,   3091,   6406,   3093,   5382,   3094,   3334,   3097,   4870,
        3107,   6278,   3109,   5254,   3110,   3206,   3113,   4742,   3121,   4486,
        3139,   6214,   3141,   5190,   3145,   4678,   3153,   4422,   3169,   4294,
        3203,   6182,   3205,   5158,   3209,   4646,   3217,   4390,   3233,   4262,
        3265,   4198,   3331,   6166,   3333,   5142,   3337,   4630,   3345,   4374,
        3361,   4246,   3393,   4182,   3457,   4150,   3587,   6158,   3589,   5134,
        3593,   4622,   3601,   4366,   3617,   4238,   3649,   4174,   3713,   4142,
        3841,   4126,   4111,   7681,   4119,   7425,   4123,   6913,   4125,   5889,
        4135,   7297,   4139,   6785,   4141,   5761,   4147,   6529,   4149,   5505,
        4153,   4993,   4167,   7233,   4171,   6721,   4173,   5697,   4179,   6465,
        4181,   5441,   4185,   4929,   4195,   6337,   4197,   5313,   4201,   4801,
        4209,   4545,   4231,   7201,   4235,   6689,   4237,   5665,   4243,   6433,
        4245,   5409,   4249,   4897,   4259,   6305,   4261,   5281,   4265,   4769,
        4273,   4513,   4291,   6241,   4293,   5217,   4297,   4705,   4305,   4449,
        4359,   7185,   4363,   6673,   4365,   5649,   4371,   6417,   4373,   5393,
        4377,   4881,   4387,   6289,   4389,   5265,   4393,   4753,   4401,   4497,
        4419,   6225,   4421,   5201,   4425,   4689,   4483,   6193,   4485,   5169,
        4489,   4657,   4615,   7177,   4619,   6665,   4621,   5641,   4627,   6409,
        4629,   5385,   4633,   4873,   4643,   6281,   4645,   5257,   4649,   4745,
        4675,   6217,   4677,   5193,   4739,   6185,   4741,   5161,   4867,   6169,
        4869,   5145,   5127,   7173,   5131,   6661,   5133,   5637,   5139,   6405,
        5141,   5381,   5155,   6277,   5157,   5253,   5187,   6213,   5251,   6181,
        5379,   6165,   5635,   6157,   6151,   7171,   6155,   6659,   6163,   6403,
        6179,   6275,   6211,   5189,   4681,   4433,   4321,   3142,   2634,   2386,
        2274,   1612,   1364,   1252,   856,    744,    496);

    private static $characterTable2 = array(
        3,      6144,   5,      5120,   6,      3072,   9,      4608,   10,     2560,
        12,     1536,   17,     4352,   18,     2304,   20,     1280,   24,     768,
        33,     4224,   34,     2176,   36,     1152,   40,     640,    48,     384,
        65,     4160,   66,     2112,   68,     1088,   72,     576,    80,     320,
        96,     192,    129,    4128,   130,    2080,   132,    1056,   136,    544,
        144,    288,    257,    4112,   258,    2064,   260,    1040,   264,    528,
        513,    4104,   514,    2056,   516,    1032,   1025,   4100,   1026,   2052,
        2049,   4098,   4097,   2050,   1028,   520,    272,    160);

    private static $barPositions = array(
        array(array(7, 2),  array(4, 3)),
        array(array(1, 10), array(0, 0)),
        array(array(9, 12), array(2, 8)),
        array(array(5, 5),  array(6, 11)),
        array(array(8, 9),  array(3, 1)),
        array(array(0, 1),  array(5, 12)),
        array(array(2, 5),  array(1, 8)),
        array(array(4, 4),  array(9, 11)),
        array(array(6, 3),  array(8, 10)),
        array(array(3, 9),  array(7, 6)),
        array(array(5, 11), array(1, 4)),
        array(array(8, 5),  array(2, 12)),
        array(array(9, 10), array(0, 2)),
        array(array(7, 1),  array(6, 7)),
        array(array(3, 6),  array(4, 9)),
        array(array(0, 3),  array(8, 6)),
        array(array(6, 4),  array(2, 7)),
        array(array(1, 1),  array(9, 9)),
        array(array(7, 10), array(5, 2)),
        array(array(4, 0),  array(3, 8)),
        array(array(6, 2),  array(0, 4)),
        array(array(8, 11), array(1, 0)),
        array(array(9, 8),  array(3, 12)),
        array(array(2, 6),  array(7, 7)),
        array(array(5, 1),  array(4, 10)),
        array(array(1, 12), array(6, 9)),
        array(array(7, 3),  array(8, 0)),
        array(array(5, 8),  array(9, 7)),
        array(array(4, 6),  array(2, 10)),
        array(array(3, 4),  array(0, 5)),
        array(array(8, 4),  array(5, 7)),
        array(array(7, 11), array(1, 9)),
        array(array(6, 0),  array(9, 6)),
        array(array(0, 6),  array(4, 8)),
        array(array(2, 1),  array(3, 2)),
        array(array(5, 9),  array(8, 12)),
        array(array(4, 11), array(6, 1)),
        array(array(9, 5),  array(7, 4)),
        array(array(3, 3),  array(1, 2)),
        array(array(0, 7),  array(2, 0)),
        array(array(1, 3),  array(4, 1)),
        array(array(6, 10), array(3, 5)),
        array(array(8, 7),  array(9, 4)),
        array(array(2, 11), array(5, 6)),
        array(array(0, 8),  array(7, 12)),
        array(array(4, 2),  array(8, 1)),
        array(array(5, 10), array(3, 0)),
        array(array(9, 3),  array(0, 9)),
        array(array(6, 5),  array(2, 4)),
        array(array(7, 8),  array(1, 7)),
        array(array(5, 0),  array(4, 5)),
        array(array(2, 3),  array(0, 10)),
        array(array(6, 12), array(9, 2)),
        array(array(3, 11), array(1, 6)),
        array(array(8, 8),  array(7, 9)),
        array(array(5, 4),  array(0, 11)),
        array(array(1, 5),  array(2, 2)),
        array(array(9, 1),  array(4, 12)),
        array(array(8, 3),  array(6, 6)),
        array(array(7, 0),  array(3, 7)),
        array(array(4, 7),  array(7, 5)),
        array(array(0, 12), array(1, 11)),
        array(array(2, 9),  array(9, 0)),
        array(array(6, 8),  array(5, 3)),
        array(array(3, 10), array(8, 2))
    );

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();

        $this->setQuietZone(true);
        $this->setThickness(9);
    }

    /**
     * Gets the Quiet zone.
     *
     * @return bool
     */
    public function getQuietZone() {
        return $this->quietZone;
    }

    /**
     * Sets the Quiet zone.
     *
     * @param bool $quietZone
     */
    public function setQuietZone($quietZone) {
        $this->quietZone = (bool)$quietZone;
    }

    /**
     * Sets the tracking code.
     *
     * @param int $barcodeIdentifier 2-digit number. 2nd digit must be 0-4
     * @param int $serviceTypeIdentifier 3 digits
     * @param int $mailerIdentifier 6 or 9 digits
     * @param int $serialNumber 9 (if mailerId is 6) or 6 digits (if mailerId is 9)
     */
    public function setTrackingCode($barcodeIdentifier, $serviceTypeIdentifier, $mailerIdentifier, $serialNumber) {
        $barcodeIdentifier = (string)(int)$barcodeIdentifier;
        $serviceTypeIdentifier = (int)$serviceTypeIdentifier;
        $mailerIdentifier = (int)$mailerIdentifier;
        $serialNumber = (string)(int)$serialNumber;

        $barcodeIdentifier = str_pad($barcodeIdentifier, 2, '0', STR_PAD_LEFT);

        if (strlen($barcodeIdentifier) !== 2) {
            throw new BCGArgumentException('Barcode Identifier must contain 2 digits.', 'barcodeIdentifier');
        }

        $barcodeIdentifierSecondNumber = $barcodeIdentifier[1];
        if ($barcodeIdentifierSecondNumber !== '0' && $barcodeIdentifierSecondNumber !== '1' && $barcodeIdentifierSecondNumber !== '2' && $barcodeIdentifierSecondNumber !== '3' && $barcodeIdentifierSecondNumber !== '4') {
            throw new BCGArgumentException('Barcode Identifier second digit must be a number between 0 and 4.', 'barcodeIdentifier');
        }

        if ($serviceTypeIdentifier < 0 || $serviceTypeIdentifier > 999) {
            throw new BCGArgumentException('Service Type Identifier must be between 0 and 999.', 'serviceTypeIdentifier');
        }

        $mailerIdentifierLength = 6;
        if ($mailerIdentifier > 899999) {
            $mailerIdentifierLength = 9;
        }

        if ($mailerIdentifierLength === 9 && strlen($serialNumber) > 6) {
            throw new BCGArgumentException('If the Serial Number has more than 6 digits, the Mailer Identifier must be lower than 900000.', 'mailerIdentifier');
        }

        if ($mailerIdentifierLength === 9) {
            if ($mailerIdentifierLength < 0 || $mailerIdentifier > 999999999) {
                throw new BCGArgumentException('Mailer Identifier must be between 0 and 999999999.', 'mailerIdentifier');
            }
        }

        $this->barcodeIdentifier = $barcodeIdentifier;
        $this->serviceTypeIdentifier = str_pad($serviceTypeIdentifier, 3, '0', STR_PAD_LEFT);
        $this->mailerIdentifier = str_pad($mailerIdentifier, $mailerIdentifierLength, '0', STR_PAD_LEFT);
        $this->serialNumber = str_pad((int)$serialNumber, $mailerIdentifierLength === 6 ? 9 : 6, '0', STR_PAD_LEFT);
    }

    /**
     * Parses the text before displaying it.
     *
     * @param mixed $text
     */
    public function parse($text) {
        parent::parse($text);

        $number = self::executeStep1($this->text, $this->barcodeIdentifier, $this->serviceTypeIdentifier, $this->mailerIdentifier, $this->serialNumber);
        $crc = self::executeStep2($number);
        $codewords = self::executeStep3($number);
        $codewords = self::executeStep4($codewords, $crc);
        $characters = self::executeStep5($codewords, $crc);
        $this->data = self::executeStep6($characters);
    }

    /**
     * Draws the barcode.
     *
     * @param resource $im
     */
    public function draw($im) {
        if ($this->quietZone) {
            $this->positionX += 9;
        }

        $c = strlen($this->data);
        for ($i = 0; $i < $c; $i++) {
            $this->drawChar($im, $this->data[$i]);
        }

        $this->drawText($im, 0, 0, $this->positionX, $this->thickness + ($this->quietZone ? 4 : 0));
    }

    /**
     * Returns the maximal size of a barcode.
     *
     * @param int $w
     * @param int $h
     * @return int[]
     */
    public function getDimension($w, $h) {
        $w += 65 * 3;
        $h += $this->thickness;

        // We remove the white on the right
        $w -= 1.56;

        if ($this->quietZone) {
            $w += 18;
            $h += 4;
        }

        return parent::getDimension($w, $h);
    }

    /**
     * Validates the input.
     */
    protected function validate() {
        // Tracking must have been entered
        if ($this->barcodeIdentifier === null || $this->serviceTypeIdentifier === null || $this->mailerIdentifier === null || $this->serialNumber === null) {
            throw new BCGParseException('intelligentmail', 'The tracking code must be set before calling the parse method.');
        }

        // Checking if all chars are allowed
        $match = array();
        if (preg_match('/[^0-9]/', $this->text, $match)) {
                throw new BCGParseException('intelligentmail', 'The character \'' . $match[0] . '\' is not allowed.');
        }

        // Must contain 0, 5, 9 or 11 chars
        $c = strlen($this->text);
        if ($c !== 0 && $c !== 5 && $c !== 9 && $c !== 11) {
            throw new BCGParseException('intelligentmail', 'Must contain 0, 5, 9, or 11 characters.');
        }

        parent::validate();
    }

    /**
     * Overloaded method for drawing special barcode.
     *
     * @param resource $im
     * @param string $code
     * @param boolean $startBar
     */
    protected function drawChar($im, $code, $startBar = true) {
        $y1 = 0;
        $y2 = 0;
        switch ($code) {
            case 'A':
                $y1 = 0;
                $y2 = $this->thickness - ($this->thickness / 2.5);
                break;
            case 'D':
                $y1 = 3.096;
                $y2 = $this->thickness - 1;
                break;
            case 'F':
                $y1 = 0;
                $y2 = $this->thickness - 1;
                break;
            case 'T':
                $y1 = 3.096;
                $y2 = $this->thickness - ($this->thickness / 2.5);
                break;
        }

        if ($this->quietZone) {
            $y1 += 2;
            $y2 += 2;
        }

        $this->drawFilledRectangle($im, $this->positionX, $y1, $this->positionX + 0.44, $y2, BCGBarcode::COLOR_FG);
        $this->positionX += 3;
    }

    /**
     * Executes Step 1: Conversion of Data Fields into Binary Data
     *
     * @param string $text
     * @param string $barcodeIdentifier
     * @param string $serviceTypeIdentifier
     * @param string $mailerIdentifier
     * @param string $serialNumber
     * @return string BCNumber
     */
    private static function executeStep1($text, $barcodeIdentifier, $serviceTypeIdentifier, $mailerIdentifier, $serialNumber) {
        $number = self::conversionRoutingCode($text);
        $number = self::conversionTrackingCode($number, $barcodeIdentifier, $serviceTypeIdentifier, $mailerIdentifier, $serialNumber);

        return $number;
    }

    /**
     * Executes Step 2: Generation of 11-Bit CRC on Binary Data
     *
     * @param $number BCNumber
     * @return int
     */
    private static function executeStep2($number) {
        $byteArray = str_pad(self::bcdecuc($number), 13, chr(0), STR_PAD_LEFT);

        $generatorPolynomial = 0x0f35;
        $frameCheckSequence = 0x07ff;
        $data = 0;
        $byteIndex = 0;
        $bit = 0;

        $data = (ord($byteArray[$byteIndex]) << 5) & 0xffff;
        for ($bit = 2; $bit < 8; $bit++) {
            if (($frameCheckSequence ^ $data) & 0x400) {
                $frameCheckSequence = ($frameCheckSequence << 1) ^ $generatorPolynomial;
            } else {
                $frameCheckSequence = ($frameCheckSequence << 1);
            }

            $frameCheckSequence &= 0x7ff;
            $data <<= 1;
            $data &= 0xffff;
        }

        for ($byteIndex = 1; $byteIndex < 13; $byteIndex++) {
            $data = (ord($byteArray[$byteIndex]) << 3) & 0xffff;
            for ($bit = 0; $bit < 8; $bit++) {
                if (($frameCheckSequence ^ $data) & 0x0400) {
                    $frameCheckSequence = ($frameCheckSequence << 1) ^ $generatorPolynomial;
                } else {
                    $frameCheckSequence = ($frameCheckSequence << 1);
                }

                $frameCheckSequence &= 0x7ff;
                $data <<= 1;
                $data &= 0xffff;
            }
        }

        return $frameCheckSequence;
    }

    /**
     * Executes Step 3: Conversion from Binary Data to Codewords
     *
     * @param string $number BCNumber
     * @return int[]
     */
    private static function executeStep3($number) {
        $codewords = array();
        $codewords[9] = (int)bcmod($number, '636');
        $number = bcdiv($number, '636', 0);

        for ($i = 8; $i >= 0; $i--) {
            $codewords[$i] = (int)bcmod($number, '1365');
            $number = bcdiv($number, '1365', 0);
        }

        return $codewords;
    }

    /**
     * Executes Step 4: Inserting Additional Information into Codewords
     *
     * @param int[] $codewords
     * @param int $crc
     * @return int[]
     */
    private static function executeStep4($codewords, $crc) {
        $codewords[9] *= 2;
        if ($crc & 0x400) {
            $codewords[0] += 659;
        }

        return $codewords;
    }

    /**
     * Executes Step 5: Conversion from Codewords to Characters
     *
     * @param int[] $codewords
     * @param int $crc
     * @return int[]
     */
    private static function executeStep5($codewords, $crc) {
        $characters = array();
        for ($i = 0; $i < 10; $i++) {
            if ($codewords[$i] <= 1286) {
                $characters[$i] = self::$characterTable1[$codewords[$i]];
            } else {
                $characters[$i] = self::$characterTable2[$codewords[$i] - 1287];
            }
        }

        for ($i = 0; $i < 10; $i++) {
            $mask = 1 << $i;
            if ($crc & $mask) {
                $characters[$i] ^= 0x1fff;
            }
        }

        return $characters;
    }

    /**
     * Executes Step 6: Conversion from Characters to the Intelligent Mail Barcode
     *
     * @param int[] $characters
     * @return string
     */
    private static function executeStep6($characters) {
        $bars = '';
        for ($i = 0; $i < 65; $i++) {
            $barPosition = self::$barPositions[$i];
            $descender = $barPosition[0];
            $ascender = $barPosition[1];
            $extenderDescender = !!($characters[$descender[0]] & (1 << $descender[1]));
            $extenderAscender = !!($characters[$ascender[0]] & (1 << $ascender[1]));

            if ($extenderDescender && $extenderAscender) {
                $bars .= 'F';
            } elseif ($extenderDescender) {
                $bars .= 'D';
            } elseif ($extenderAscender) {
                $bars .= 'A';
            } else {
                $bars .= 'T';
            }
        }

        return $bars;
    }

    /**
     * Converts the routing code zipcode.
     *
     * @param string $zipcode
     * @return string BCNumber
     */
    private static function conversionRoutingCode($zipcode) {
        $number = $zipcode;
        switch (strlen($zipcode)) {
            case 11:
                $number = bcadd($number, '1000000000', 0);
            case 9:
                $number = bcadd($number, '100000', 0);
            case 5:
                $number = bcadd($number, '1', 0);
            default:
                return $number;
        }
    }

    /**
     * Converts the tracking code number.
     *
     * @param string $number BCNumber
     * @param string $barcodeIdentifier
     * @param string $serviceTypeIdentifier
     * @param string $mailerIdentifier
     * @param string $serialNumber
     * @return string BCNumber
     */
    private static function conversionTrackingCode($number, $barcodeIdentifier, $serviceTypeIdentifier, $mailerIdentifier, $serialNumber) {
        $number = bcmul($number, 10, 0);
        $number = bcadd($number, $barcodeIdentifier[0], 0);
        $number = bcmul($number, 5, 0);
        $number = bcadd($number, $barcodeIdentifier[1], 0);

        $temp = $serviceTypeIdentifier . $mailerIdentifier . $serialNumber;
        for ($i = 0; $i < 18; $i++) {
            $number = bcmul($number, 10, 0);
            $number = bcadd($number, $temp[$i], 0);
        }

        return $number;
    }

    /**
     * Transforms a BCNumber into unsigned char*.
     *
     * @param string $dec BCNumber
     * @param string
     */
    private static function bcdecuc($dec) {
        $last = bcmod($dec, 256);
        $remain = bcdiv(bcsub($dec, $last), 256, 0);

        if ($remain == 0) {
            return pack('C', $last);
        } else {
            return self::bcdecuc($remain) . pack('C', $last);
        }
    }
}
?>