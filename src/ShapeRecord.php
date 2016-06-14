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

class ShapeRecord {
    private $SHPFile = NULL;
    private $DBFFile = NULL;

    public $recordNumber = NULL;
    private $shapeType = NULL;

    public $lastError = "";

    public $SHPData = array();
    public $DBFData = array();

    public function __construct($shapeType) {
        $this->shapeType = $shapeType;
    }

    public function loadFromFile(&$SHPFile, &$DBFFile) {
        $this->SHPFile = $SHPFile;
        $this->DBFFile = $DBFFile;
        $this->_loadHeaders();

        switch ($this->shapeType) {
            case 0:
                $this->_loadNullRecord();
                break;
            case 1:
                $this->_loadPointRecord();
                break;
            case 21:
                $this->_loadPointMRecord();
                break;
            case 11:
                $this->_loadPointZRecord();
                break;
            case 3:
                $this->_loadPolyLineRecord();
                break;
            case 23:
                $this->_loadPolyLineMRecord();
                break;
            case 13:
                $this->_loadPolyLineZRecord();
                break;
            case 5:
                $this->_loadPolygonRecord();
                break;
            case 25:
                $this->_loadPolygonMRecord();
                break;
            case 15:
                $this->_loadPolygonZRecord();
                break;
            case 8:
                $this->_loadMultiPointRecord();
                break;
            case 28:
                $this->_loadMultiPointMRecord();
                break;
            case 18:
                $this->_loadMultiPointZRecord();
                break;
            default:
                $this->setError(sprintf("The Shape Type '%s' is not supported.", $this->shapeType));
                break;
        }
        $this->_loadDBFData();
    }

    public function saveToFile(&$SHPFile, &$DBFFile, $recordNumber) {
        $this->SHPFile = $SHPFile;
        $this->DBFFile = $DBFFile;
        $this->recordNumber = $recordNumber;
        $this->_saveHeaders();

        switch ($this->shapeType) {
            case 0:
                $this->_saveNullRecord();
                break;
            case 1:
                $this->_savePointRecord();
                break;
            case 21:
                $this->_savePointMRecord();
                break;
            case 11:
                $this->_savePointZRecord();
                break;
            case 3:
                $this->_savePolyLineRecord();
                break;
            case 23:
                $this->_savePolyLineMRecord();
                break;
            case 13:
                $this->_savePolyLineZRecord();
                break;
            case 5:
                $this->_savePolygonRecord();
                break;
            case 25:
                $this->_savePolygonMRecord();
                break;
            case 15:
                $this->_savePolygonZRecord();
                break;
            case 8:
                $this->_saveMultiPointRecord();
                break;
            case 28:
                $this->_saveMultiPointMRecord();
                break;
            case 18:
                $this->_saveMultiPointZRecord();
                break;
            default:
                $this->setError(sprintf("The Shape Type '%s' is not supported.", $this->shapeType));
                break;
        }
        $this->_saveDBFData();
    }

    public function updateDBFInfo($header) {
        $tmp = $this->DBFData;
        unset($this->DBFData);
        $this->DBFData = array();
        reset($header);
        while (list($key, $value) = each($header)) {
            $this->DBFData[$value[0]] = (isset($tmp[$value[0]])) ? $tmp[$value[0]] : "";
        }
    }

    private function _loadHeaders() {
        $this->recordNumber = Util::loadData("N", fread($this->SHPFile, 4));
        $tmp = Util::loadData("N", fread($this->SHPFile, 4)); //We read the length of the record
        $this->shapeType = Util::loadData("V", fread($this->SHPFile, 4));
    }

    private function _saveHeaders() {
        fwrite($this->SHPFile, pack("N", $this->recordNumber));
        fwrite($this->SHPFile, pack("N", $this->getContentLength()));
        fwrite($this->SHPFile, pack("V", $this->shapeType));
    }

    private function _loadPoint() {
        $data = array();

        $data["x"] = Util::loadData("d", fread($this->SHPFile, 8));
        $data["y"] = Util::loadData("d", fread($this->SHPFile, 8));

        return $data;
    }

    private function _loadPointM() {
        $data = array();

        $data["x"] = Util::loadData("d", fread($this->SHPFile, 8));
        $data["y"] = Util::loadData("d", fread($this->SHPFile, 8));
        $data["m"] = Util::loadData("d", fread($this->SHPFile, 8));

        return $data;
    }

    private function _loadPointZ() {
        $data = array();

        $data["x"] = Util::loadData("d", fread($this->SHPFile, 8));
        $data["y"] = Util::loadData("d", fread($this->SHPFile, 8));
        $data["z"] = Util::loadData("d", fread($this->SHPFile, 8));
        $data["m"] = Util::loadData("d", fread($this->SHPFile, 8));

        return $data;
    }

    private function _savePoint($data) {
        fwrite($this->SHPFile, Util::packDouble($data["x"]));
        fwrite($this->SHPFile, Util::packDouble($data["y"]));
    }

    private function _savePointM($data) {
        fwrite($this->SHPFile, Util::packDouble($data["x"]));
        fwrite($this->SHPFile, Util::packDouble($data["y"]));
        fwrite($this->SHPFile, Util::packDouble($data["m"]));
    }

    private function _savePointZ($data) {
        fwrite($this->SHPFile, Util::packDouble($data["x"]));
        fwrite($this->SHPFile, Util::packDouble($data["y"]));
        fwrite($this->SHPFile, Util::packDouble($data["z"]));
        fwrite($this->SHPFile, Util::packDouble($data["m"]));
    }

    private function _saveMeasure($data) {
        fwrite($this->SHPFile, Util::packDouble($data["m"]));
    }

    private function _saveZCoordinate($data) {
        fwrite($this->SHPFile, Util::packDouble($data["z"]));
    }

    private function _loadNullRecord() {
        $this->SHPData = array();
    }

    private function _saveNullRecord() {
        //Don't save anything
    }

    private function _loadPointRecord() {
        $this->SHPData = $this->_loadPoint();
    }

    private function _loadPointMRecord() {
        $this->SHPData = $this->_loadPointM();
    }

    private function _loadPointZRecord() {
        $this->SHPData = $this->_loadPointZ();
    }

    private function _savePointRecord() {
        $this->_savePoint($this->SHPData);
    }

    private function _savePointMRecord() {
        $this->_savePointM($this->SHPData);
    }

    private function _savePointZRecord() {
        $this->_savePointZ($this->SHPData);
    }

    private function _loadMultiPointRecord() {
        $this->SHPData = array();
        $this->SHPData["xmin"] = Util::loadData("d", fread($this->SHPFile, 8));
        $this->SHPData["ymin"] = Util::loadData("d", fread($this->SHPFile, 8));
        $this->SHPData["xmax"] = Util::loadData("d", fread($this->SHPFile, 8));
        $this->SHPData["ymax"] = Util::loadData("d", fread($this->SHPFile, 8));

        $this->SHPData["numpoints"] = Util::loadData("V", fread($this->SHPFile, 4));

        for ($i = 0; $i <= $this->SHPData["numpoints"]; $i++) {
            $this->SHPData["points"][] = $this->_loadPoint();
        }
    }

    private function _loadMultiPointMZRecord( $type ) {

        $this->SHPData[$type."min"] = Util::loadData("d", fread($this->SHPFile, 8));
        $this->SHPData[$type."max"] = Util::loadData("d", fread($this->SHPFile, 8));

        for ($i = 0; $i <= $this->SHPData["numpoints"]; $i++) {
            $this->SHPData["points"][$i][$type] = Util::loadData("d", fread($this->SHPFile, 8));
        }
    }

    private function _loadMultiPointMRecord() {
        $this->_loadMultiPointRecord();

        $this->_loadMultiPointMZRecord("m");
    }

    private function _loadMultiPointZRecord() {
        $this->_loadMultiPointRecord();

        $this->_loadMultiPointMZRecord("z");
        $this->_loadMultiPointMZRecord("m");
    }

    private function _saveMultiPointRecord() {
        fwrite($this->SHPFile, pack("dddd", $this->SHPData["xmin"], $this->SHPData["ymin"], $this->SHPData["xmax"], $this->SHPData["ymax"]));

        fwrite($this->SHPFile, pack("V", $this->SHPData["numpoints"]));

        for ($i = 0; $i <= $this->SHPData["numpoints"]; $i++) {
            $this->_savePoint($this->SHPData["points"][$i]);
        }
    }

    private function _saveMultiPointMZRecord( $type ) {

        fwrite($this->SHPFile, pack("dd", $this->SHPData[$type."min"], $this->SHPData[$type."max"]));

        for ($i = 0; $i <= $this->SHPData["numpoints"]; $i++) {
            fwrite($this->SHPFile, Util::packDouble($this->SHPData["points"][$type]));
        }
    }

    private function _saveMultiPointMRecord() {
        $this->_saveMultiPointRecord();

        $this->_saveMultiPointMZRecord("m");
    }

    private function _saveMultiPointZRecord() {
        $this->_saveMultiPointRecord();

        $this->_saveMultiPointMZRecord("z");
        $this->_saveMultiPointMZRecord("m");
    }

    private function _loadPolyLineRecord() {
        $this->SHPData = array();
        $this->SHPData["xmin"] = Util::loadData("d", fread($this->SHPFile, 8));
        $this->SHPData["ymin"] = Util::loadData("d", fread($this->SHPFile, 8));
        $this->SHPData["xmax"] = Util::loadData("d", fread($this->SHPFile, 8));
        $this->SHPData["ymax"] = Util::loadData("d", fread($this->SHPFile, 8));

        $this->SHPData["numparts"]  = Util::loadData("V", fread($this->SHPFile, 4));
        $this->SHPData["numpoints"] = Util::loadData("V", fread($this->SHPFile, 4));

        for ($i = 0; $i < $this->SHPData["numparts"]; $i++) {
            $this->SHPData["parts"][$i] = Util::loadData("V", fread($this->SHPFile, 4));
        }

        $firstIndex = ftell($this->SHPFile);
        $readPoints = 0;
        reset($this->SHPData["parts"]);
        while (list($partIndex, $partData) = each($this->SHPData["parts"])) {
            if (!isset($this->SHPData["parts"][$partIndex]["points"]) || !is_array($this->SHPData["parts"][$partIndex]["points"])) {
                $this->SHPData["parts"][$partIndex] = array();
                $this->SHPData["parts"][$partIndex]["points"] = array();
            }
            while (!in_array($readPoints, $this->SHPData["parts"]) && ($readPoints < ($this->SHPData["numpoints"])) && !feof($this->SHPFile)) {
                $this->SHPData["parts"][$partIndex]["points"][] = $this->_loadPoint();
                $readPoints++;
            }
        }

        fseek($this->SHPFile, $firstIndex + ($readPoints*16));
    }

    private function _loadPolyLineMZRecord( $type ) {

        $this->SHPData[$type."min"] = Util::loadData("d", fread($this->SHPFile, 8));
        $this->SHPData[$type."max"] = Util::loadData("d", fread($this->SHPFile, 8));

        $firstIndex = ftell($this->SHPFile);
        $readPoints = 0;
        reset($this->SHPData["parts"]);
        while (list($partIndex, $partData) = each($this->SHPData["parts"])) {
            while (!in_array($readPoints, $this->SHPData["parts"]) && ($readPoints < ($this->SHPData["numpoints"])) && !feof($this->SHPFile)) {
                $this->SHPData["parts"][$partIndex]["points"][$readPoints][$type] = Util::loadData("d", fread($this->SHPFile, 8));
                $readPoints++;
            }
        }

        fseek($this->SHPFile, $firstIndex + ($readPoints*24));
    }

    private function _loadPolyLineMRecord() {
        $this->_loadPolyLineRecord();

        $this->_loadPolyLineMZRecord("m");
    }

    private function _loadPolyLineZRecord() {
        $this->_loadPolyLineRecord();

        $this->_loadPolyLineMZRecord("z");
        $this->_loadPolyLineMZRecord("m");
    }

    private function _savePolyLineRecord() {
        fwrite($this->SHPFile, pack("dddd", $this->SHPData["xmin"], $this->SHPData["ymin"], $this->SHPData["xmax"], $this->SHPData["ymax"]));

        fwrite($this->SHPFile, pack("VV", $this->SHPData["numparts"], $this->SHPData["numpoints"]));

        for ($i = 0; $i < $this->SHPData["numparts"]; $i++) {
            fwrite($this->SHPFile, pack("V", count($this->SHPData["parts"][$i])-1));
        }

        foreach ($this->SHPData["parts"] as $partData){
            reset($partData["points"]);
            while (list($pointIndex, $pointData) = each($partData["points"])) {
                $this->_savePoint($pointData);
            }
        }
    }

    private function _savePolyLineMZRecord( $type ) {
        fwrite($this->SHPFile, pack("dd", $this->SHPData[$type."min"], $this->SHPData[$type."max"]));

        foreach ($this->SHPData["parts"] as $partData){
            reset($partData["points"]);
            while (list($pointIndex, $pointData) = each($partData["points"])) {
                fwrite($this->SHPFile, Util::packDouble($pointData[$type]));
            }
        }
    }

    private function _savePolyLineMRecord() {
        $this->_savePolyLineRecord();

        $this->_savePolyLineMZRecord("m");
    }

    private function _savePolyLineZRecord() {
        $this->_savePolyLineRecord();

        $this->_savePolyLineMZRecord("z");
        $this->_savePolyLineMZRecord("m");
    }

    private function _loadPolygonRecord() {
        $this->_loadPolyLineRecord();
    }

    private function _loadPolygonMRecord() {
        $this->_loadPolyLineMRecord();
    }

    private function _loadPolygonZRecord() {
        $this->_loadPolyLineZRecord();
    }

    private function _savePolygonRecord() {
        $this->_savePolyLineRecord();
    }

    private function _savePolygonMRecord() {
        $this->_savePolyLineMRecord();
    }

    private function _savePolygonZRecord() {
        $this->_savePolyLineZRecord();
    }

    public function addPoint($point, $partIndex = 0) {
        switch ($this->shapeType) {
            case 0:
                //Don't add anything
                break;
            case 1:
            case 11:
            case 21:
                if (in_array($this->shapeType,array(11,21)) && !isset($point["m"])) $point["m"] = 0.0; // no_value
                if (in_array($this->shapeType,array(11)) && !isset($point["z"])) $point["z"] = 0.0; // no_value
                //Substitutes the value of the current point
                $this->SHPData = $point;
                break;
            case 3:
            case 5:
            case 13:
            case 15:
            case 23:
            case 25:
                if (in_array($this->shapeType,array(13,15,23,25)) && !isset($point["m"])) $point["m"] = 0.0; // no_value
                if (in_array($this->shapeType,array(13,15)) && !isset($point["z"])) $point["z"] = 0.0; // no_value

                //Adds a new point to the selected part
                if (!isset($this->SHPData["xmin"]) || ($this->SHPData["xmin"] > $point["x"])) $this->SHPData["xmin"] = $point["x"];
                if (!isset($this->SHPData["ymin"]) || ($this->SHPData["ymin"] > $point["y"])) $this->SHPData["ymin"] = $point["y"];
                if (isset($point["m"]) && (!isset($this->SHPData["mmin"]) || ($this->SHPData["mmin"] > $point["m"]))) $this->SHPData["mmin"] = $point["m"];
                if (isset($point["z"]) && (!isset($this->SHPData["zmin"]) || ($this->SHPData["zmin"] > $point["z"]))) $this->SHPData["zmin"] = $point["z"];
                if (!isset($this->SHPData["xmax"]) || ($this->SHPData["xmax"] < $point["x"])) $this->SHPData["xmax"] = $point["x"];
                if (!isset($this->SHPData["ymax"]) || ($this->SHPData["ymax"] < $point["y"])) $this->SHPData["ymax"] = $point["y"];
                if (isset($point["m"]) && (!isset($this->SHPData["mmax"]) || ($this->SHPData["mmax"] < $point["m"]))) $this->SHPData["mmax"] = $point["m"];
                if (isset($point["z"]) && (!isset($this->SHPData["zmax"]) || ($this->SHPData["zmax"] < $point["z"]))) $this->SHPData["zmax"] = $point["z"];

                $this->SHPData["parts"][$partIndex]["points"][] = $point;

                $this->SHPData["numparts"] = count($this->SHPData["parts"]);
                $this->SHPData["numpoints"] = 1 + (isset($this->SHPData["numpoints"])?$this->SHPData["numpoints"]:0);
                break;
            case 8:
            case 18:
            case 28:
                if (in_array($this->shapeType,array(18,28)) && !isset($point["m"])) $point["m"] = 0.0; // no_value
                if (in_array($this->shapeType,array(18)) && !isset($point["z"])) $point["z"] = 0.0; // no_value

                //Adds a new point
                if (!isset($this->SHPData["xmin"]) || ($this->SHPData["xmin"] > $point["x"])) $this->SHPData["xmin"] = $point["x"];
                if (!isset($this->SHPData["ymin"]) || ($this->SHPData["ymin"] > $point["y"])) $this->SHPData["ymin"] = $point["y"];
                if (isset($point["m"]) && (!isset($this->SHPData["mmin"]) || ($this->SHPData["mmin"] > $point["m"]))) $this->SHPData["mmin"] = $point["m"];
                if (isset($point["z"]) && (!isset($this->SHPData["zmin"]) || ($this->SHPData["zmin"] > $point["z"]))) $this->SHPData["zmin"] = $point["z"];
                if (!isset($this->SHPData["xmax"]) || ($this->SHPData["xmax"] < $point["x"])) $this->SHPData["xmax"] = $point["x"];
                if (!isset($this->SHPData["ymax"]) || ($this->SHPData["ymax"] < $point["y"])) $this->SHPData["ymax"] = $point["y"];
                if (isset($point["m"]) && (!isset($this->SHPData["mmax"]) || ($this->SHPData["mmax"] < $point["m"]))) $this->SHPData["mmax"] = $point["m"];
                if (isset($point["z"]) && (!isset($this->SHPData["zmax"]) || ($this->SHPData["zmax"] < $point["z"]))) $this->SHPData["zmax"] = $point["z"];

                $this->SHPData["points"][] = $point;
                $this->SHPData["numpoints"] = 1 + (isset($this->SHPData["numpoints"])?$this->SHPData["numpoints"]:0);
                break;
            default:
                $this->setError(sprintf("The Shape Type '%s' is not supported.", $this->shapeType));
                break;
        }
    }

    public function deletePoint($pointIndex = 0, $partIndex = 0) {
        switch ($this->shapeType) {
            case 0:
                //Don't delete anything
                break;
            case 1:
            case 11:
            case 21:
                //Sets the value of the point to zero
                $this->SHPData["x"] = 0.0;
                $this->SHPData["y"] = 0.0;
                if (in_array($this->shapeType,array(11,21))) $this->SHPData["m"] = 0.0;
                if (in_array($this->shapeType,array(11))) $this->SHPData["z"] = 0.0;
                break;
            case 3:
            case 5:
            case 13:
            case 15:
            case 23:
            case 25:
                //Deletes the point from the selected part, if exists
                if (isset($this->SHPData["parts"][$partIndex]) && isset($this->SHPData["parts"][$partIndex]["points"][$pointIndex])) {
                    for ($i = $pointIndex; $i < (count($this->SHPData["parts"][$partIndex]["points"]) - 1); $i++) {
                        $this->SHPData["parts"][$partIndex]["points"][$i] = $this->SHPData["parts"][$partIndex]["points"][$i + 1];
                    }
                    unset($this->SHPData["parts"][$partIndex]["points"][count($this->SHPData["parts"][$partIndex]["points"]) - 1]);

                    $this->SHPData["numparts"] = count($this->SHPData["parts"]);
                    $this->SHPData["numpoints"]--;
                }
                break;
            case 8:
            case 18:
            case 28:
                //Deletes the point, if exists
                if (isset($this->SHPData["points"][$pointIndex])) {
                    for ($i = $pointIndex; $i < (count($this->SHPData["points"]) - 1); $i++) {
                        $this->SHPData["points"][$i] = $this->SHPData["points"][$i + 1];
                    }
                    unset($this->SHPData["points"][count($this->SHPData["points"]) - 1]);

                    $this->SHPData["numpoints"]--;
                }
                break;
            default:
                $this->setError(sprintf("The Shape Type '%s' is not supported.", $this->shapeType));
                break;
        }
    }

    public function getContentLength() {
        // The content length for a record is the length of the record contents section measured in 16-bit words.
        // one coordinate makes 4 16-bit words (64 bit double)
        switch ($this->shapeType) {
            case 0:
                $result = 0;
                break;
            case 1:
                $result = 10;
                break;
            case 21:
                $result = 10 + 4;
                break;
            case 11:
                $result = 10 + 8;
                break;
            case 3:
            case 5:
                $result = 22 + 2*count($this->SHPData["parts"]);
                for ($i = 0; $i < count($this->SHPData["parts"]); $i++) {
                    $result += 8*count($this->SHPData["parts"][$i]["points"]);
                }
                break;
            case 23:
            case 25:
                $result = 22 + (2*4) + 2*count($this->SHPData["parts"]);
                for ($i = 0; $i < count($this->SHPData["parts"]); $i++) {
                    $result += (8+4)*count($this->SHPData["parts"][$i]["points"]);
                }
                break;
            case 13:
            case 15:
                $result = 22 + (4*4) + 2*count($this->SHPData["parts"]);
                for ($i = 0; $i < count($this->SHPData["parts"]); $i++) {
                    $result += (8+8)*count($this->SHPData["parts"][$i]["points"]);
                }
                break;
            case 8:
                $result = 20 + 8*count($this->SHPData["points"]);
                break;
            case 28:
                $result = 20 + (2*4) + (8+4)*count($this->SHPData["points"]);
                break;
            case 18:
                $result = 20 + (4*4) + (8+8)*count($this->SHPData["points"]);
                break;
            default:
                $result = false;
                $this->setError(sprintf("The Shape Type '%s' is not supported.", $this->shapeType));
                break;
        }
        return $result;
    }

    private function _loadDBFData() {
        $this->DBFData = @dbase_get_record_with_names($this->DBFFile, $this->recordNumber);
        unset($this->DBFData["deleted"]);
    }

    private function _saveDBFData() {
        unset($this->DBFData["deleted"]);
        if ($this->recordNumber <= dbase_numrecords($this->DBFFile)) {
            if (!dbase_replace_record($this->DBFFile, array_values($this->DBFData), $this->recordNumber)) {
                $this->setError("I wasn't possible to update the information in the DBF file.");
            }
        } else {
            if (!dbase_add_record($this->DBFFile, array_values($this->DBFData))) {
                $this->setError("I wasn't possible to add the information to the DBF file.");
            }
        }
    }

    public function setError($error) {
        $this->lastError = $error;
        return false;
    }
}
