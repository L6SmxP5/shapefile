<?php
/**
 * BytesFall ShapeFiles library
 *
 * The library implements the 2D variants of the ShapeFile format as defined in
 * http://www.esri.com/library/whitepapers/pdfs/shapefile.pdf.
 * The library currently supports reading and editing of ShapeFiles and the
 * Associated information (DBF file).
 *
 * @package bfShapeFiles
 * @version 0.0.2
 * @link http://bfshapefiles.sourceforge.net/
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2-or-later
 *
 * Copyright 2006-2007 Ovidio <ovidio AT users.sourceforge.net>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, you can download one from
 * http://www.gnu.org/copyleft/gpl.html.
 *
 */
namespace ShapeFile;

class Util {
    public static function loadData($type, $data) {
        if (!$data) {
            return $data;
        }
        $tmp = unpack($type, $data);
        return current($tmp);
    }

    public static function swap($binValue) {
        $result = $binValue{strlen($binValue) - 1};
        for ($i = strlen($binValue) - 2; $i >= 0; $i--) {
            $result .= $binValue{$i};
        }

        return $result;
    }

    public static function packDouble($value, $mode = 'LE') {
        $value = (double) $value;
        $bin = pack("d", $value);

        //We test if the conversion of an integer (1) is done as LE or BE by default
        switch (pack('L', 1)) {
            case pack('V', 1): //Little Endian
                $result = ($mode == 'LE') ? $bin : self::swap($bin);
                break;
            case pack('N', 1): //Big Endian
                $result = ($mode == 'BE') ? $bin : self::swap($bin);
                break;
            default: //Some other thing, we just return false
                $result = FALSE;
        }

        return $result;
    }
}