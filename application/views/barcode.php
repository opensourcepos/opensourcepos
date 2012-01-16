<?php
/*===========================================================================*/
/*      PHP Barcode Image Generator v1.0 [9/28/2000]
        Copyright (C)2000 by Charles J. Scheffold - cs@wsia.fm
		Modified by Chris Muench [11/9/08] to work with codeignitor application framework

        ---
        UPDATE 09/21/2002 by Laurent NAVARRO - ln@altidev.com - http://www.altidev.com
        Updated to be compatible with register_globals = off and on
        ---
        UPDATE 4/6/2001 - Important Note! This script was written with the assumption
        that "register_globals = On" is defined in your PHP.INI file! It will not 
        work as-is      and as described unless this is set. My PHP came with this 
        enabled by default, but apparently many people have turned it off. Either 
        turn it on or modify the startup code to pull the CGI variables in the old 
        fashioned way (from the HTTP* arrays). If you just want to use the functions 
        and pass the variables yourself, well then go on with your bad self.
        ---
        
        This code is hereby released into the public domain.
        Use it, abuse it, just don't get caught using it for something stupid.


        The only barcode type currently supported is Code 3 of 9. Don't ask about 
        adding support for others! This is a script I wrote for my own use. I do 
        plan to add more types as time permits but currently I only require 
        Code 3 of 9 for my purposes. Just about every scanner on the market today
        can read it.


        PARAMETERS:
        -----------
        $barcode        = [required] The barcode you want to generate


        $type           = (default=0) It's 0 for Code 3 of 9 (the only one supported)
        
        $width          = (default=160) Width of image in pixels. The image MUST be wide
                                  enough to handle the length of the given value. The default
                                  value will probably be able to display about 6 digits. If you
                                  get an error message, make it wider!


        $height         = (default=80) Height of image in pixels
        
        $format         = (default=jpeg) Can be "jpeg", "png", or "gif"
        
        $quality        = (default=100) For JPEG only: ranges from 0-100


        $text           = (default='') 0 Enter any string to be displayed
        


        NOTE: You must have GD-1.8 or higher compiled into PHP
        in order to use PNG and JPEG. GIF images only work with
        GD-1.5 and lower. (http://www.boutell.com)


        ANOTHER NOTE: If you actually intend to print the barcodes 
        and scan them with a scanner, I highly recommend choosing 
        JPEG with a quality of 100. Most browsers can't seem to print 
        a PNG without mangling it beyond recognition. 


        USAGE EXAMPLES FOR ANY PLAIN OLD HTML DOCUMENT:
        -----------------------------------------------


        <IMG SRC="index.php?c=barcode&barcode=HELLO&quality=75">


        <IMG SRC="index.php?c=barcode&barcode=123456&width=320&height=200">
                
        
*/
/*=============================================================================*/


//-----------------------------------------------------------------------------
// Startup code
//-----------------------------------------------------------------------------


if(isset($_GET["text"])) $text=$_GET["text"];
if(isset($_GET["format"])) $format=$_GET["format"];
if(isset($_GET["quality"])) $quality=$_GET["quality"];
if(isset($_GET["width"])) $width=$_GET["width"];
if(isset($_GET["height"])) $height=$_GET["height"];
if(isset($_GET["type"])) $type=$_GET["type"];
if(isset($_GET["barcode"])) $barcode=$_GET["barcode"];




if (!isset ($text)) $text = '';
if (!isset ($type)) $type = 1;
if (empty ($quality)) $quality = 100;
if (empty ($width)) $width = 160;
if (empty ($height)) $height = 80;
if (!empty ($format)) $format = strtoupper ($format);
        else $format="PNG";


switch ($type)
{
        default:
                $type = 1;
        case 1:
                Barcode39 ($barcode, $width, $height, $quality, $format, $text);
                break;          
}


//-----------------------------------------------------------------------------
// Generate a Code 3 of 9 barcode
//-----------------------------------------------------------------------------
function Barcode39 ($barcode, $width, $height, $quality, $format, $text)
{
        switch ($format)
        {
                default:
                        $format = "JPEG";
                case "JPEG": 
                        header ("Content-type: image/jpeg");
                        break;
                case "PNG":
                        header ("Content-type: image/png");
                        break;
                case "GIF":
                        header ("Content-type: image/gif");
                        break;
        }


        $im = ImageCreate ($width, $height)
    or die ("Cannot Initialize new GD image stream");
        $White = ImageColorAllocate ($im, 255, 255, 255);
        $Black = ImageColorAllocate ($im, 0, 0, 0);
        //ImageColorTransparent ($im, $White);
        ImageInterLace ($im, 1);



        $NarrowRatio = 20;
        $WideRatio = 55;
        $QuietRatio = 35;


        $nChars = (strlen($barcode)+2) * ((6 * $NarrowRatio) + (3 * $WideRatio) + ($QuietRatio));
        $Pixels = $width / $nChars;
        $NarrowBar = (int)(20 * $Pixels);
        $WideBar = (int)(55 * $Pixels);
        $QuietBar = (int)(35 * $Pixels);


        $ActualWidth = (($NarrowBar * 6) + ($WideBar*3) + $QuietBar) * (strlen ($barcode)+2);
        
        if (($NarrowBar == 0) || ($NarrowBar == $WideBar) || ($NarrowBar == $QuietBar) || ($WideBar == 0) || ($WideBar == $QuietBar) || ($QuietBar == 0))
        {
                ImageString ($im, 1, 0, 0, "Image is too small!", $Black);
                OutputImage ($im, $format, $quality);
                exit;
        }
        
        $CurrentBarX = (int)(($width - $ActualWidth) / 2);
        $Color = $White;
        $BarcodeFull = "*".strtoupper ($barcode)."*";
        settype ($BarcodeFull, "string");
        
        $FontNum = 3;
        $FontHeight = ImageFontHeight ($FontNum);
        $FontWidth = ImageFontWidth ($FontNum);
        
        if ($text != '')
        {
                $CenterLoc = (int)(($width) / 2) - (int)(($FontWidth * strlen($text)) / 2);
                ImageString ($im, $FontNum, $CenterLoc, $height-$FontHeight, "$text", $Black);
        }
        

        for ($i=0; $i<strlen($BarcodeFull); $i++)
        {
                $StripeCode = Code39 ($BarcodeFull[$i]);


                for ($n=0; $n < 9; $n++)
                {
                        if ($Color == $White) $Color = $Black;
                        else $Color = $White;


                        switch ($StripeCode[$n])
                        {
                                case '0':
                                        ImageFilledRectangle ($im, $CurrentBarX, 0, $CurrentBarX+$NarrowBar, $height-1-$FontHeight-2, $Color);
                                        $CurrentBarX += $NarrowBar;
                                        break;


                                case '1':
                                        ImageFilledRectangle ($im, $CurrentBarX, 0, $CurrentBarX+$WideBar, $height-1-$FontHeight-2, $Color);
                                        $CurrentBarX += $WideBar;
                                        break;
                        }
                }


                $Color = $White;
                ImageFilledRectangle ($im, $CurrentBarX, 0, $CurrentBarX+$QuietBar, $height-1-$FontHeight-2, $Color);
                $CurrentBarX += $QuietBar;
        }


        OutputImage ($im, $format, $quality);
}


//-----------------------------------------------------------------------------
// Output an image to the browser
//-----------------------------------------------------------------------------
function OutputImage ($im, $format, $quality)
{
        switch ($format)
        {
                case "JPEG": 
                        ImageJPEG ($im, "", $quality);
                        break;
                case "PNG":
                        ImagePNG ($im);
                        break;
                case "GIF":
                        ImageGIF ($im);
                        break;
        }
}


//-----------------------------------------------------------------------------
// Returns the Code 3 of 9 value for a given ASCII character
//-----------------------------------------------------------------------------
function Code39 ($Asc)
{
        switch ($Asc)
        {
                case ' ':
                        return "011000100";     
                case '$':
                        return "010101000";             
                case '%':
                        return "000101010"; 
                case '*':
                        return "010010100"; // * Start/Stop
                case '+':
                        return "010001010"; 
                case '|':
                        return "010000101"; 
                case '.':
                        return "110000100"; 
                case '/':
                        return "010100010"; 
                case '0':
                        return "000110100"; 
                case '1':
                        return "100100001"; 
                case '2':
                        return "001100001"; 
                case '3':
                        return "101100000"; 
                case '4':
                        return "000110001"; 
                case '5':
                        return "100110000"; 
                case '6':
                        return "001110000"; 
                case '7':
                        return "000100101"; 
                case '8':
                        return "100100100"; 
                case '9':
                        return "001100100"; 
                case 'A':
                        return "100001001"; 
                case 'B':
                        return "001001001"; 
                case 'C':
                        return "101001000";
                case 'D':
                        return "000011001";
                case 'E':
                        return "100011000";
                case 'F':
                        return "001011000";
                case 'G':
                        return "000001101";
                case 'H':
                        return "100001100";
                case 'I':
                        return "001001100";
                case 'J':
                        return "000011100";
                case 'K':
                        return "100000011";
                case 'L':
                        return "001000011";
                case 'M':
                        return "101000010";
                case 'N':
                        return "000010011";
                case 'O':
                        return "100010010";
                case 'P':
                        return "001010010";
                case 'Q':
                        return "000000111";
                case 'R':
                        return "100000110";
                case 'S':
                        return "001000110";
                case 'T':
                        return "000010110";
                case 'U':
                        return "110000001";
                case 'V':
                        return "011000001";
                case 'W':
                        return "111000000";
                case 'X':
                        return "010010001";
                case 'Y':
                        return "110010000";
                case 'Z':
                        return "011010000";
                default:
                        return "011000100"; 
        }
}


?>