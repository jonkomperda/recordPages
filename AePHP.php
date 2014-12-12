<?

// AePHP class allows you to read aem files generated with ae v3.0
// class tested on PHP 5.3.14 and PHP 5.4.4
// Last revision: 4 june 2013
// © Andreu Biosca Llahí

class AePHP {

    // $bParseWithThrows = false -> recommended for quick readings
    // $bParseWithThrows = true -> recommended if you want to verify data is not
    // corrupted. You can also invoque bUNIQUEConstraintsAreRespected() and 
    // bKeysAreRespected() to check this constraints. EMPTY constraint is
    // checked automatically when $bParseWithThrows = true

    public static function parse($sFilePath, $sMemoryLimitMB = "", $bParseWithThrows = false) {
        if ($sMemoryLimitMB != "") {
            ini_set("memory_limit", $sMemoryLimitMB);
        }
        $file_type = ucfirst(substr($sFilePath, strlen($sFilePath) - 3, 3));
        if ($file_type != "Aem") {
            throw new Exception('Not valid extension');
        } else {
            if (fopen($sFilePath, "rb")) {
                $fhandle = fopen($sFilePath, "rb");
            } else {
                throw new Exception('Error opening ' . $sFilePath);
            }

            fseek($fhandle, 0);
            $sAemVersionAndHeaders = fread($fhandle, 18);
            $sVersion = bin2hex(substr($sAemVersionAndHeaders, 0, 1)) . "." .
                    bin2hex(substr($sAemVersionAndHeaders, 1, 1));
            if ($sVersion != "3.0") {
                throw new Exception('Not supported version');
            }
            $sHeaderMedia = bin2hex(substr($sAemVersionAndHeaders, 2, 8));
            $sHeaderMedia_N = "";
            for ($i = 7; $i >= 0; $i--) {
                $sHeaderMedia_N .= substr($sHeaderMedia, 2 * $i, 2);
            }
            $sHeaderIcon = bin2hex(substr($sAemVersionAndHeaders, 10, 8));
            $sHeaderIcon_N = "";
            for ($i = 7; $i >= 0; $i--) {
                $sHeaderIcon_N .= substr($sHeaderIcon, 2 * $i, 2);
            }
            $iNbBytesMedia = base_convert($sHeaderMedia_N, 16, 10);
            $iNbBytesIcon = base_convert($sHeaderIcon_N, 16, 10);
            if ((18 + $iNbBytesMedia + $iNbBytesIcon) >= filesize($sFilePath)) {
                throw new Exception('Error with media/icon headers');
            }
            fseek($fhandle, 18 + $iNbBytesMedia + $iNbBytesIcon);


            $iCurrentByte = 0;

            $aBytesVERSION_ENCODING = fread($fhandle, 3);


            $aBytes = fread($fhandle, filesize($sFilePath) - (18 + $iNbBytesMedia + $iNbBytesIcon) - 3);


            fclose($fhandle);


            $oReturn = new $file_type(new AePHP(), $aBytesVERSION_ENCODING, $aBytes, $bParseWithThrows);


            $oReturn->setNbBytesHeaders(new AePHP(), $iNbBytesMedia, $iNbBytesIcon);
            $oReturn->setSVersion($sVersion);


            $oReturn->setSFilePath($sFilePath);

            $aBytes = null;

            if ($bParseWithThrows) {
                // check cell values sintax and EMPTY constraint:
                $bDefaultValuesAndCellValuesAreValid = $oReturn->bDefaultValuesAndCellValuesAreValid();
                if (is_string($bDefaultValuesAndCellValuesAreValid)) {
                    throw new Exception('Error: ' . $bDefaultValuesAndCellValuesAreValid);
                }
            }

            return $oReturn;
        }
    }

    private function __construct() {
        
    }

}

class Ae2 {

    private $sFilePath;
    private $sVersion;
    private $iEncodig;
    private $iNbColumns;
    private $iNbRows;
    private $aValues;
    private $aColType;
    private $aColCanBeNULL;
    private $aColIsUNIQUE;
    private $aColDefaultValue;
    private $aColWidth;
    private $bWindowIsMaximized;
    private $iWindowWidth;
    private $iWindowHeight;
    private $iRowheight;
    private $aColSorting;
    private $sComments;
    private $aKeys;
    private $iSyncMode;
    private $sSyncKey;

    // GETTERS ++++++++++++++++++++++

    public function getSFilePath() {
        return $this->sFilePath;
    }

    public function getSVersion() {
        return $this->sVersion;
    }

    public function getIEncoding() {
        return $this->iEncodig;
    }

    public function getINbRows() {
        return $this->iNbRows;
    }

    public function getINbColumns() {
        return $this->iNbColumns;
    }

    public function getAValues() {
        return $this->aValues;
    }

    public function getCell($sColName, $iRowIndex) {
        if (isset($this->aValues[$sColName][$iRowIndex])) {
            return $this->aValues[$sColName][$iRowIndex];
        } else {
            return null;
        }
    }

    public function getAColType($sColName) {
        return $this->aColType[$sColName];
    }

    public function getSColCanBeNULL($sColName) {
        return $this->aColCanBeNULL[$sColName];
    }

    public function getSColIsUNIQUE($sColName) {
        return $this->aColIsUNIQUE[$sColName];
    }

    public function getSColDefaultValue($sColName) {
        return $this->aColDefaultValue[$sColName];
    }

    public function getSColWidth($sColName) {
        return $this->aColWidth[$sColName];
    }

    public function getBWindowIsMaximized() {
        return $this->bWindowIsMaximized;
    }

    public function getIWindowWidth() {
        return $this->iWindowWidth;
    }

    public function getIWindowHeight() {
        return $this->iWindowHeight;
    }

    public function getIRowHeight() {
        return $this->iRowheight;
    }

    public function getAColSorting($sColName) {
        return $this->aColSorting[$sColName];
    }

    public function getSComments() {
        return $this->sComments;
    }

    public function getAKeys() {
        return $this->aKeys;
    }

    public function getISyncMode() {
        return $this->iSyncMode;
    }

    public function getSSyncKey() {
        return $this->sSyncKey;
    }

    // ++++++++++++++++++++++++++++++
    // SETTERS ++++++++++++++++++++++

    public function setSFilePath($sFilePath) {
        $this->sFilePath = $sFilePath;
    }

    // ++++++++++++++++++++++++++++++
    // REPLACE ++++++++++++++++++++++
    public function replaceCell($sColName, $iRowIndex, $sNewValue) {
        if (!isset($this->aValues[$sColName][$iRowIndex])) {
            throw new Exception('Cell was not found');
        }
        $this->aValues[$sColName][$iRowIndex] = $sNewValue;
    }

    public function replaceDefaultValue($sColName, $sNewValue) {
        if (!isset($this->aColDefaultValue[$sColName])) {
            throw new Exception('Default value was not found');
        }
        $this->aColDefaultValue[$sColName] = $sNewValue;
    }

    public function increaseRows($increment = 1) {
        $this->iNbRows += $increment;
    }

    public function decreaseRows($decrement = 1) {
        $this->iNbRows -= $decrement;
    }

    // ++++++++++++++++++++++++++++++

    public function __construct($aephp, $aBytesVERSION_ENCODING, $aBytes, $bParseWithThrows) {

        if (get_class($aephp) != "AePHP") {
            throw new Exception('Use AePHP::parse to parse a file!');
        }

        $this->sVersion = intval(bin2hex($aBytesVERSION_ENCODING[0])) . "." .
                intval(bin2hex($aBytesVERSION_ENCODING[1]));

        $this->iEncodig = intval(bin2hex($aBytesVERSION_ENCODING[2]));

        if (($bParseWithThrows) && ($this->sVersion != "3.0")) {
            throw new Exception('Not supported version');
        }

        if (($bParseWithThrows) && ($this->iEncodig != 0) &&
                ($this->iEncodig != 1) &&
                ($this->iEncodig != 2) &&
                ($this->iEncodig != 3) &&
                ($this->iEncodig != 4)) {
            throw new Exception('Not supported encoding');
        }

        if (bin2hex($aBytesVERSION_ENCODING[2]) == "01") {
            $aBytes = str_split(iconv('UTF-16LE', 'UTF-8', $aBytes));
        }

        if (bin2hex($aBytesVERSION_ENCODING[2]) == "02") {
            $aBytes = str_split(iconv('UTF-16BE', 'UTF-8', $aBytes));
        }

        if (bin2hex($aBytesVERSION_ENCODING[2]) == "03") {
            $aBytes = str_split(iconv('UTF-32LE', 'UTF-8', $aBytes));
        }

        if (bin2hex($aBytesVERSION_ENCODING[2]) == "04") {
            $aBytes = str_split(iconv('UTF-32BE', 'UTF-8', $aBytes));
        }

        
        
        $iCharPos = 0;

        $bIsTheBreakByte = false;

        $iNbColumns = 0;

        $sReadedData = "";

        while (!$bIsTheBreakByte) {
            $bIsTheBreakByte = bin2hex($aBytes[$iCharPos]) == "00";
            if (!$bIsTheBreakByte) {
                $sReadedData.=$aBytes[$iCharPos];
            }

            $iCharPos++;
        }

        $iNbColumns = $sReadedData;

        if (($bParseWithThrows) && (!ctype_digit($sReadedData))) {
            throw new Exception('Number of columns must be formed by digits');
        }

        if (($bParseWithThrows) && ($iNbColumns < 0)) {
            throw new Exception('Negative value indicating number of columns');
        }

        // Reading columns names:

        $bIsTheBreakByte = false;

        $values = array();

        $iNbColumnsReaded = 0;

        $aColsNames = array();

        while ($iNbColumnsReaded < $iNbColumns) {

            $sReadedData = "";
            while (!$bIsTheBreakByte) {

                $bIsTheBreakByte = bin2hex($aBytes[$iCharPos]) == "00";
                if (!$bIsTheBreakByte) {
                    $sReadedData.=$aBytes[$iCharPos];
                }
                $iCharPos++;
            }

            if (($bParseWithThrows) && ($sReadedData == "")) {
                throw new Exception('Column name cannot be NULL');
            }

            $values[$sReadedData] = array();

            array_push($aColsNames, $sReadedData);

            $iNbColumnsReaded++;

            $bIsTheBreakByte = false;
        }

        if ($bParseWithThrows) {
            if (count(array_unique($aColsNames)) < count($aColsNames)) {
                // Array has duplicates
                throw new Exception('Column name must be UNIQUE');
            }
        }

        // reading number of rows:

        $iNbRows = 0;

        $sReadedData = "";

        while (!$bIsTheBreakByte) {

            $bIsTheBreakByte = bin2hex($aBytes[$iCharPos]) == "00";
            if (!$bIsTheBreakByte) {
                $sReadedData.=$aBytes[$iCharPos];
            }

            $iCharPos++;
        }

        $iNbRows = $sReadedData;

        if (($bParseWithThrows) && (!ctype_digit($sReadedData))) {
            throw new Exception('Number of rows must be formed by digits');
        }

        if (($bParseWithThrows) && (($iNbRows > 0) && ($iNbColumns == 0))) {
            throw new Exception('There can be no rows if there are no columns');
        }

        // reading rows values:

        $bIsTheBreakByte = false;

        $iNbRowsReaded = 0;

        $currentAttributeIndex = 0;

        $keys = array_keys($values);

        while ($iNbRowsReaded < $iNbRows) {

            $sReadedData = "";

            while (!$bIsTheBreakByte) {
                $bIsTheBreakByte = bin2hex($aBytes[$iCharPos]) == "00";
                if (!$bIsTheBreakByte) {
                    $sReadedData.=$aBytes[$iCharPos];
                }
                $iCharPos++;
            }

            array_push($values[$keys[$currentAttributeIndex]], $sReadedData);

            $currentAttributeIndex++;

            if ($currentAttributeIndex == $iNbColumns) {
                $currentAttributeIndex = 0;
                $iNbRowsReaded++;
            }


            $bIsTheBreakByte = false;
        }


        // Column properties:

        $bExistsOptionalBlock = !(($iCharPos) == strlen($aBytes));


        if (!$bExistsOptionalBlock) {
            // Columns type:
            $aColsType = array();
            for ($j = 0; $j < $iNbColumns; $j++) {
                $sTypeStr = array();
                array_push($sTypeStr, "0");
                $aColsType[$keys[$j]] = $sTypeStr;
            }
            // Columns can be NULL:
            $aColumnsCanBeNull = array();
            for ($j = 0; $j < $iNbColumns; $j++) {
                $aColumnsCanBeNull[$keys[$j]] = "1";
            }
            // Columns are UNIQUE:
            $aColumnIsUNIQUE = array();
            for ($j = 0; $j < $iNbColumns; $j++) {
                $aColumnIsUNIQUE[$keys[$j]] = "0";
            }
            // Column default value:
            $aColumnDefaultValue = array();
            for ($j = 0; $j < $iNbColumns; $j++) {
                $aColumnDefaultValue[$keys[$j]] = "";
            }
            // Columns width:
            $aColumnWidth = array();
            for ($j = 0; $j < $iNbColumns; $j++) {
                $aColumnWidth[$keys[$j]] = "205";
            }

            $bWinIsMax = false;
            $sWindowW = strval((205 * max(1, $iNbColumns)) + 30);
            $sWindowH = "297";
            $iRow_height = "24";

            // Columns sorting:
            $aColumnSorting = array();
            for ($j = 0; $j < $iNbColumns; $j++) {
                $sNoOrder = array();
                array_push($sNoOrder, "0");
                $aColumnSorting[$keys[$j]] = $sNoOrder;
            }

            // Comments:
            $sComments = "";

            // Keys:
            $aKeys = array();
            $aKeys[0] = array();
            $aKeys[1] = array();

            $iSyncMode_ = 0;
            $sSyncKey_ = "";
        } else {

            // Columns types:

            $aColsType = array();

            for ($j = 0; $j < $iNbColumns; $j++) {
                $aColsType[$keys[$j]] = array();
            }

            for ($iNbColumnsReaded = 0; $iNbColumnsReaded < $iNbColumns; $iNbColumnsReaded++) {
                $sReadedData = "";
                while (!$bIsTheBreakByte) {
                    $bIsTheBreakByte = bin2hex($aBytes[$iCharPos]) == "00";
                    if (!$bIsTheBreakByte) {
                        $sReadedData.=$aBytes[$iCharPos];
                    }
                    $iCharPos++;
                }
                array_push($aColsType[$keys[$iNbColumnsReaded]], $sReadedData);

                if (($bParseWithThrows) && ($sReadedData != "0") && ($sReadedData != "1") && ($sReadedData != "2") && ($sReadedData != "3") &&
                        ($sReadedData != "4") && ($sReadedData != "4Z") && ($sReadedData != "5") && ($sReadedData != "5Z") &&
                        ($sReadedData != "6") && ($sReadedData != "7") && ($sReadedData != "8") && ($sReadedData != "9") &&
                        ($sReadedData != "10") && ($sReadedData != "11") && ($sReadedData != "12") && ($sReadedData != "13")) {
                    throw new Exception('Unknown column type');
                }

                if ($sReadedData == "6") {

                    $bIsTheBreakByte = false;

                    $sReadedData = "";

                    while (!$bIsTheBreakByte) {
                        $bIsTheBreakByte = bin2hex($aBytes[$iCharPos]) == "00";
                        if (!$bIsTheBreakByte) {
                            $sReadedData.=$aBytes[$iCharPos];
                        }
                        $iCharPos++;
                    }

                    array_push($aColsType[$keys[$iNbColumnsReaded]], $sReadedData);

                    if ($bParseWithThrows) {
                        if ($sReadedData != 0) {
                            throw new Exception('An enumeration must be string type');
                        }
                    }

                    $bIsTheBreakByte = false;

                    $sReadedData = "";

                    while (!$bIsTheBreakByte) {
                        $bIsTheBreakByte = bin2hex($aBytes[$iCharPos]) == "00";
                        if (!$bIsTheBreakByte) {
                            $sReadedData.=$aBytes[$iCharPos];
                        }
                        $iCharPos++;
                    }

                    $nbEnumerators = $sReadedData;

                    if ($bParseWithThrows) {
                        if ($nbEnumerators <= 0) {
                            throw new Exception('An enumeration must have at least 1 enumerator');
                        }
                    }

                    $aEnumerators = array();

                    for ($nbEnumeratorsReaded = 0; $nbEnumeratorsReaded < $nbEnumerators; $nbEnumeratorsReaded++) {
                        $bIsTheBreakByte = false;
                        $sReadedData = "";
                        while (!$bIsTheBreakByte) {
                            $bIsTheBreakByte = bin2hex($aBytes[$iCharPos]) == "00";
                            if (!$bIsTheBreakByte) {
                                $sReadedData.=$aBytes[$iCharPos];
                            }
                            $iCharPos++;
                        }
                        array_push($aColsType[$keys[$iNbColumnsReaded]], $sReadedData);
                        array_push($aEnumerators, $sReadedData);
                    }

                    if ($bParseWithThrows) {
                        if (count(array_unique($aEnumerators)) < count($aEnumerators)) {
                            // Array has duplicates
                            throw new Exception('An enumerator must be UNIQUE');
                        }
                    }
                }
                $bIsTheBreakByte = false;
            }

            for ($j = 0; $j < $iNbColumns; $j++) {
                if ($aColsType[$keys[$j]][0] == "6") {
                    $nbEnumCol = count($aColsType[$keys[$j]])-2;
                    for ($k = 0; $k < $iNbRows; $k++) {
                        if ($bParseWithThrows) {
                            if (($values[$keys[$j]][$k] + 2) < 2) {
                                throw new Exception('Cell pointing at invalid enumerator');
                            }
                            if (($values[$keys[$j]][$k] + 2) > ($nbEnumCol + 1)) {
                                throw new Exception('Cell pointing at invalid enumerator');
                            }
                        }
                        $values[$keys[$j]][$k] = $aColsType[$keys[$j]][$values[$keys[$j]][$k] + 2];
                    }
                }
            }

            // Columns can be NULL:

            $aColumnsCanBeNull = array();

            for ($j = 0; $j < $iNbColumns; $j++) {
                $aColumnsCanBeNull[$keys[$j]] = "";
            }

            $sReadedData = "";

            while (!$bIsTheBreakByte) {
                $bIsTheBreakByte = bin2hex($aBytes[$iCharPos]) == "00";
                if (!$bIsTheBreakByte) {
                    $sReadedData.=$aBytes[$iCharPos];
                }
                $iCharPos++;
            }

            for ($iNbColumnsReaded = 0; $iNbColumnsReaded < $iNbColumns; $iNbColumnsReaded++) {
                $aColumnsCanBeNull[$keys[$iNbColumnsReaded]] = substr($sReadedData, $iNbColumnsReaded, 1);
                if ($bParseWithThrows) {
                    if ((substr($sReadedData, $iNbColumnsReaded, 1) != "0") &&
                            (substr($sReadedData, $iNbColumnsReaded, 1) != "1")) {
                        throw new Exception('Column can be NULL has to be expressed with 0 or 1');
                    }
                    if ((substr($sReadedData, $iNbColumnsReaded, 1) == "1") &&
                            ($aColsType[$keys[$iNbColumnsReaded]][0] == "2")) {
                        throw new Exception('Boolean column cannot be NULL');
                    }
                }
            }

            $bIsTheBreakByte = false;

            // Columns are UNIQUE:

            $aColumnIsUNIQUE = array();

            for ($j = 0; $j < $iNbColumns; $j++) {
                $aColumnIsUNIQUE[$keys[$j]] = "";
            }

            $sReadedData = "";

            while (!$bIsTheBreakByte) {
                $bIsTheBreakByte = bin2hex($aBytes[$iCharPos]) == "00";
                if (!$bIsTheBreakByte) {
                    $sReadedData.=$aBytes[$iCharPos];
                }
                $iCharPos++;
            }

            for ($iNbColumnsReaded = 0; $iNbColumnsReaded < $iNbColumns; $iNbColumnsReaded++) {
                $aColumnIsUNIQUE[$keys[$iNbColumnsReaded]] = substr($sReadedData, $iNbColumnsReaded, 1);
                if ($bParseWithThrows) {
                    if ((substr($sReadedData, $iNbColumnsReaded, 1) != "0") &&
                            (substr($sReadedData, $iNbColumnsReaded, 1) != "1")) {
                        throw new Exception('Column is UNIQUE has to be expressed with 0 or 1');
                    }
                }
            }

            $bIsTheBreakByte = false;

            // Columns default value:

            $aColumnDefaultValue = array();

            for ($j = 0; $j < $iNbColumns; $j++) {
                $aColumnDefaultValue[$keys[$j]] = "";
            }

            for ($j = 0; $j < $iNbColumns; $j++) {
                $sReadedData = "";

                while (!$bIsTheBreakByte) {
                    $bIsTheBreakByte = bin2hex($aBytes[$iCharPos]) == "00";
                    if (!$bIsTheBreakByte) {
                        $sReadedData.=$aBytes[$iCharPos];
                    }
                    $iCharPos++;
                }

                if ($aColsType[$keys[$j]][0] == "6") {
                    if ($bParseWithThrows) {
                        if (($sReadedData + 2) < 2) {
                            throw new Exception('Cell pointing at invalid enumerator');
                        }
                        if (($sReadedData + 2) > ($nbEnumerators + 1)) {
                            throw new Exception('Cell pointing at invalid enumerator');
                        }
                    }
                    $sReadedData = $aColsType[$keys[$j]][$sReadedData + 2];
                }

                $aColumnDefaultValue[$keys[$j]] = $sReadedData;

                if ($bParseWithThrows) {
                    if (($aColumnsCanBeNull[$keys[$j]] == "1") &&
                            ($aColsType[$keys[$j]][0] == "1") &&
                            $sReadedData == "♨") {
                        throw new Exception('Autonumeric column cannot be NULL');
                    }
                    if (($aColumnIsUNIQUE[$keys[$j]] == "0") &&
                            ($aColsType[$keys[$j]][0] == "1") &&
                            $sReadedData == "♨") {
                        throw new Exception('Autonumeric column must be UNIQUE');
                    }
                }

                $bIsTheBreakByte = false;
            }

            // VISUALIZATION: Columns width, cols height, window maximized, 
            // window width, window height, steppers

            $aColumnWidth = array();

            for ($j = 0; $j < $iNbColumns; $j++) {
                $aColumnWidth[$keys[$j]] = "";
            }

            for ($j = 0; $j < $iNbColumns; $j++) {
                $sReadedData = "";

                while (!$bIsTheBreakByte) {
                    $bIsTheBreakByte = bin2hex($aBytes[$iCharPos]) == "00";
                    if (!$bIsTheBreakByte) {
                        $sReadedData.=$aBytes[$iCharPos];
                    }
                    $iCharPos++;
                }

                if ($bParseWithThrows) {
                    if (!ctype_digit($sReadedData)) {
                        throw new Exception('Invalid column width');
                    }
                    if ($sReadedData < 27) {
                        throw new Exception('Invalid column width');
                    }
                }

                $aColumnWidth[$keys[$j]] = $sReadedData;

                $bIsTheBreakByte = false;
            }

            if (intval($iNbColumns) == 0)
                $iCharPos+=1;

            // Max?:

            $sReadedData = "";
            $bIsTheBreakByte = false;
            while (!$bIsTheBreakByte) {
                $bIsTheBreakByte = bin2hex($aBytes[$iCharPos]) == "00";
                if (!$bIsTheBreakByte) {
                    $sReadedData.=$aBytes[$iCharPos];
                }
                $iCharPos++;
            }

            if ($bParseWithThrows) {
                if (($sReadedData != "0") && ($sReadedData != "1")) {
                    throw new Exception('Invalid "window is maximized" indicator');
                }
            }

            $bWinIsMax = $sReadedData;
            $bIsTheBreakByte = false;

            // Window W:

            $sReadedData = "";
            $bIsTheBreakByte = false;
            while (!$bIsTheBreakByte) {
                $bIsTheBreakByte = bin2hex($aBytes[$iCharPos]) == "00";
                if (!$bIsTheBreakByte) {
                    $sReadedData.=$aBytes[$iCharPos];
                }
                $iCharPos++;
            }

            if ($bParseWithThrows) {
                if (!ctype_digit($sReadedData)) {
                    throw new Exception('Invalid window width');
                }
                if ($sReadedData < 205) {
                    throw new Exception('Invalid window width');
                }
            }

            $sWindowW = $sReadedData;
            $bIsTheBreakByte = false;

            // Window H:

            $sReadedData = "";
            $bIsTheBreakByte = false;
            while (!$bIsTheBreakByte) {
                $bIsTheBreakByte = bin2hex($aBytes[$iCharPos]) == "00";
                if (!$bIsTheBreakByte) {
                    $sReadedData.=$aBytes[$iCharPos];
                }
                $iCharPos++;
            }

            if ($bParseWithThrows) {
                if (!ctype_digit($sReadedData)) {
                    throw new Exception('Invalid window height');
                }
                if ($sReadedData < 297) {
                    throw new Exception('Invalid window height');
                }
            }

            $sWindowH = $sReadedData;

            // rowHeight:

            $sReadedData = "";
            $bIsTheBreakByte = false;
            while (!$bIsTheBreakByte) {
                $bIsTheBreakByte = bin2hex($aBytes[$iCharPos]) == "00";
                if (!$bIsTheBreakByte) {
                    $sReadedData.=$aBytes[$iCharPos];
                }
                $iCharPos++;
            }

            if ($bParseWithThrows) {
                if (($sReadedData != "24") && ($sReadedData != "78")) {
                    throw new Exception('Invalid row height');
                }
            }

            $iRow_height = $sReadedData;

            // Steppers:

            $sReadedData = "";
            $bIsTheBreakByte = false;
            while (!$bIsTheBreakByte) {
                $bIsTheBreakByte = bin2hex($aBytes[$iCharPos]) == "00";
                if (!$bIsTheBreakByte) {
                    $sReadedData.=$aBytes[$iCharPos];
                }
                $iCharPos++;
            }
            $iNbStepperFields = intval($sReadedData);

            if ($bParseWithThrows) {
                if (!ctype_digit($sReadedData)) {
                    throw new Exception('Invalid number of stepper columns');
                }
            }

            $bIsTheBreakByte = false;
            $aStepperIndexs = array();
            for ($j = 0; $j < $iNbStepperFields; $j++) {
                $sReadedData = "";
                while (!$bIsTheBreakByte) {
                    $bIsTheBreakByte = bin2hex($aBytes[$iCharPos]) == "00";
                    if (!$bIsTheBreakByte) {
                        $sReadedData.=$aBytes[$iCharPos];
                    }
                    $iCharPos++;
                }
                $bIsTheBreakByte = false;
                $sIndexAtr = $sReadedData;
                array_push($aStepperIndexs, $sIndexAtr);
                if ($bParseWithThrows) {
                    if (!ctype_digit($sIndexAtr)) {
                        throw new Exception('Invalid stepper index');
                    }
                    if ($sIndexAtr >= $iNbColumns) {
                        throw new Exception('Invalid stepper index');
                    }
                }
                $sReadedData = "";
                while (!$bIsTheBreakByte) {
                    $bIsTheBreakByte = bin2hex($aBytes[$iCharPos]) == "00";
                    if (!$bIsTheBreakByte) {
                        $sReadedData.=$aBytes[$iCharPos];
                    }
                    $iCharPos++;
                }
                $bIsTheBreakByte = false;
                $sSteppUP = $sReadedData;
                if ($bParseWithThrows) {
                    if (!ctype_digit($sSteppUP)) {
                        throw new Exception('Invalid stepper up value');
                    }
                }
                $sReadedData = "";
                while (!$bIsTheBreakByte) {
                    $bIsTheBreakByte = bin2hex($aBytes[$iCharPos]) == "00";
                    if (!$bIsTheBreakByte) {
                        $sReadedData.=$aBytes[$iCharPos];
                    }
                    $iCharPos++;
                }
                $bIsTheBreakByte = false;
                $sSteppDOWN = $sReadedData;
                if ($bParseWithThrows) {
                    if ($sSteppDOWN >= 0) {
                        throw new Exception('Invalid stepper down value');
                    }
                }
                array_push($aColsType[$keys[$sIndexAtr]], $sSteppUP);
                array_push($aColsType[$keys[$sIndexAtr]], $sSteppDOWN);
            }
            if ($bParseWithThrows) {
                if (count(array_unique($aStepperIndexs)) < count($aStepperIndexs)) {
                    // Array has duplicates
                    throw new Exception('Repeated stepper index');
                }
            }
            // Columns order:

            $aColumnSorting = array();

            for ($j = 0; $j < $iNbColumns; $j++) {
                $aColumnSorting[$keys[$j]] = array();
            }

            $aPriorities = array();
            $iMaxPriority = 0;
            for ($j = 0; $j < $iNbColumns; $j++) {
                $sReadedData = "";

                while (!$bIsTheBreakByte) {
                    $bIsTheBreakByte = bin2hex($aBytes[$iCharPos]) == "00";
                    if (!$bIsTheBreakByte) {
                        $sReadedData.=$aBytes[$iCharPos];
                    }
                    $iCharPos++;
                }

                array_push($aColumnSorting[$keys[$j]], $sReadedData);
                if ($bParseWithThrows) {
                    if (!ctype_digit($sReadedData)) {
                        throw new Exception('Invalid order priority');
                    }
                    if (($sReadedData < 0) || ($sReadedData > $iNbColumns)) {
                        throw new Exception('Invalid order priority');
                    }
                    $iMaxPriority = max($iMaxPriority, $sReadedData);
                }

                $bIsTheBreakByte = false;

                if ($sReadedData != "0") {
                    array_push($aPriorities, $sReadedData);
                    $sReadedData = "";

                    while (!$bIsTheBreakByte) {
                        $bIsTheBreakByte = bin2hex($aBytes[$iCharPos]) == "00";
                        if (!$bIsTheBreakByte) {
                            $sReadedData.=$aBytes[$iCharPos];
                        }
                        $iCharPos++;
                    }

                    if ($bParseWithThrows) {
                        if (($sReadedData != "0") && ($sReadedData != "1")) {
                            throw new Exception('Invalid ASC/DESC indicator');
                        }
                    }
                    array_push($aColumnSorting[$keys[$j]], $sReadedData);

                    $bIsTheBreakByte = false;
                }
            }

            if ($bParseWithThrows) {
                for ($iCprior = 1; $iCprior <= $iMaxPriority; $iCprior++) {
                    if (!in_array($iCprior, $aPriorities)) {
                        throw new Exception('Priority ' . $iCprior . ' not found');
                    }
                }
            }

            if ($bParseWithThrows) {
                if (count(array_unique($aPriorities)) < count($aPriorities)) {
                    // Array has duplicates
                    throw new Exception('Repeated order priorities');
                }
            }

            // Comments:

            $sReadedData = "";

            while (!$bIsTheBreakByte) {
                $bIsTheBreakByte = bin2hex($aBytes[$iCharPos]) == "00";
                if (!$bIsTheBreakByte) {
                    $sReadedData.=$aBytes[$iCharPos];
                }
                $iCharPos++;
            }

            $sComments = $sReadedData;

            $bIsTheBreakByte = false;

            // Keys:

            $aKeys = array();
            $aKeys[0] = array();
            $aKeys[1] = array();

            $sReadedData = "";

            while (!$bIsTheBreakByte) {
                $bIsTheBreakByte = bin2hex($aBytes[$iCharPos]) == "00";
                if (!$bIsTheBreakByte) {
                    $sReadedData.=$aBytes[$iCharPos];
                }
                $iCharPos++;
            }

            $iNbKeys = intval($sReadedData);

            if ($bParseWithThrows) {
                if (!ctype_digit($sReadedData)) {
                    throw new Exception('Invalid number of keys');
                }
            }

            $bIsTheBreakByte = false;
            $aKeysNames = array();
            for ($j = 0; $j < $iNbKeys; $j++) {

                // Key name:

                $sReadedData = "";
                while (!$bIsTheBreakByte) {
                    $bIsTheBreakByte = bin2hex($aBytes[$iCharPos]) == "00";
                    if (!$bIsTheBreakByte) {
                        $sReadedData.=$aBytes[$iCharPos];
                    }
                    $iCharPos++;
                }
                $bIsTheBreakByte = false;
                if ($bParseWithThrows) {
                    if ($sReadedData == "") {
                        throw new Exception('Key name cannot be NULL');
                    }
                }
                array_push($aKeys[0], $sReadedData);
                array_push($aKeysNames, $sReadedData);

                // NbCols that affects:

                $sReadedData = "";
                while (!$bIsTheBreakByte) {
                    $bIsTheBreakByte = bin2hex($aBytes[$iCharPos]) == "00";
                    if (!$bIsTheBreakByte) {
                        $sReadedData.=$aBytes[$iCharPos];
                    }
                    $iCharPos++;
                }
                $bIsTheBreakByte = false;
                $iNbColsAffects = intval($sReadedData);

                if ($bParseWithThrows) {
                    if ($iNbColsAffects < 2) {
                        throw new Exception('Key must affect at least 2 columns');
                    }
                    if ($iNbColsAffects > $iNbColumns) {
                        throw new Exception('Key is affecting inexistent columns');
                    }
                }

                // Cols that affects:

                $aColsAffects = array();
                for ($k = 0; $k < $iNbColsAffects; $k++) {
                    $sReadedData = "";
                    while (!$bIsTheBreakByte) {
                        $bIsTheBreakByte = bin2hex($aBytes[$iCharPos]) == "00";
                        if (!$bIsTheBreakByte) {
                            $sReadedData.=$aBytes[$iCharPos];
                        }
                        $iCharPos++;
                    }
                    $bIsTheBreakByte = false;
                    array_push($aColsAffects, $keys[$sReadedData]);
                }

                if ($bParseWithThrows) {
                    if (count(array_unique($aColsAffects)) < count($aColsAffects)) {
                        // Array has duplicates
                        throw new Exception('A key has repeated columns that affect');
                    }
                    for ($kk = 0; $kk < $j; $kk++) {
                        if ((array_intersect($aKeys[1][$kk], $aColsAffects) == $aColsAffects) ||
                                (array_intersect($aKeys[1][$kk], $aColsAffects) == $aKeys[1][$kk])) {
                            throw new Exception('There are 2 keys that affect the same columns');
                        }
                    }
                }
                array_push($aKeys[1], $aColsAffects);
            }

            if ($bParseWithThrows) {
                if (count(array_unique($aKeysNames)) < count($aKeysNames)) {
                    // Array has duplicates
                    throw new Exception('Repeated key name');
                }
            }

            $sReadedData = "";

            while (!$bIsTheBreakByte) {
                $bIsTheBreakByte = bin2hex($aBytes[$iCharPos]) == "00";
                if (!$bIsTheBreakByte) {
                    $sReadedData.=$aBytes[$iCharPos];
                }
                $iCharPos++;
            }

            $iSyncMode_ = intval($sReadedData);

            if ($bParseWithThrows) {
                if (($iSyncMode_ != 0) && ($iSyncMode_ != 1) && ($iSyncMode_ != 2)) {
                    throw new Exception('Invalid sync mode');
                }
            }

            $bIsTheBreakByte = false;

            $sReadedData = "";

            while (!$bIsTheBreakByte) {
                $bIsTheBreakByte = bin2hex($aBytes[$iCharPos]) == "00";
                if (!$bIsTheBreakByte) {
                    $sReadedData.=$aBytes[$iCharPos];
                }
                $iCharPos++;
            }

            $sSyncKey_ = $sReadedData;

            if ($bParseWithThrows) {
                if ($sSyncKey_ != "") {
                    if (!isset($values[$sSyncKey_])) {
                        throw new Exception('Invalid sync key');
                    }
                    if ($aColumnIsUNIQUE[$sSyncKey_] != "1") {
                        throw new Exception('Invalid sync key');
                    }
                    $aColTypeRev = $aColsType[$sSyncKey_];

                    if (($aColTypeRev[0] == "9") || ($aColTypeRev[0] == "10") || ($aColTypeRev[0] == "11")
                            || ($aColTypeRev[0] == "12") || ($aColTypeRev[0] == "13") || ($aColTypeRev[0] == "6")) {
                        throw new Exception('Invalid sync key');
                    }
                }
            }
        }

        $this->iSyncMode = $iSyncMode_;
        $this->sSyncKey = $sSyncKey_;
        $this->iRowheight = $iRow_height;
        $this->aKeys = $aKeys;
        $this->sComments = $sComments;
        $this->aColSorting = $aColumnSorting;
        $this->iWindowWidth = intval($sWindowW);
        $this->iWindowHeight = intval($sWindowH);
        $this->bWindowIsMaximized = $bWinIsMax == "1";
        $this->aColWidth = $aColumnWidth;
        $this->aColDefaultValue = $aColumnDefaultValue;
        $this->aColIsUNIQUE = $aColumnIsUNIQUE;
        $this->aColCanBeNULL = $aColumnsCanBeNull;
        $this->aColType = $aColsType;
        $this->aValues = $values;
        $this->iNbColumns = count($values);
        if ($this->iNbColumns > 0) {
            $this->iNbRows = count($values[$keys[0]]);
        } else {
            $this->iNbRows = 0;
        }
    }

    public function removeAllRows() {
        foreach (array_keys($this->getAValues()) as $sColName) {
            $this->aValues[$sColName] = array();
        }
    }

    public function bUNIQUEConstraintsAreRespected() {
        foreach (array_keys($this->getAValues()) as $sColName) {
            if ($this->getSColIsUNIQUE($sColName) == "1") {
                $aColumnsNames = array();
                array_push($aColumnsNames, $sColName);
                if (!$this->bColumnsAreUnique($aColumnsNames)) {
                    return 'error in column "' . $sColName . '", does not respect UNIQUE constraint';
                }
            }
        }
        return true;
    }

    public function bKeysAreRespected() {
        $_aKeys = $this->getAKeys();
        for ($i = 0; $i < count($_aKeys[0]); $i++) {
            $aColumnsNames = array();
            foreach ($_aKeys[1][$i] as $sKeyName) {
                array_push($aColumnsNames, $sKeyName);
            }
            if (!$this->bColumnsAreUnique($aColumnsNames)) {
                return 'key "' . $_aKeys[0][$i] . '", is not respected';
            }
        }
        return true;
    }

    public function bDefaultValuesAndCellValuesAreValid() {
        // check cell values sintax and EMPTY constraint:
        foreach (array_keys($this->getAValues()) as $sColName) {
            for ($i = 0; $i < $this->getINbRows(); $i++) {
                if (!$this->bCellDefaultValueIsValid($sColName, false, $i)) {
                    return 'error in column "' . $sColName . '" / cell "' . $i . 
                            '", invalid cell value: "'.$this->getCell($sColName, $i).'"';
                }
            }
            if (!$this->bCellDefaultValueIsValid($sColName, true)) {
                return 'error in column "' . $sColName . '", invalid default value';
            }
        }
        return true;
    }

    public function bCellDefaultValueIsValid($sColName, $bIsDefaultValue, $iRowIndex = "") {
        $_aColType = $this->getAColType($sColName);
        $sColType = $_aColType[0];
        if ($bIsDefaultValue) {
            $sValue = $this->getSColDefaultValue($sColName);
        } else {
            $sValue = $this->getCell($sColName, $iRowIndex);
            if (($this->getSColCanBeNULL($sColName) == "0") && ($sValue == "")) {
                return false;
            }
        }
        if ($sValue == "")
            return true;
        switch ($sColType) {
            case "0":
                return true;
                break;
            case "1":
                if ($bIsDefaultValue && ($sValue == "♨"))
                    return true;
                if ((!$bIsDefaultValue) && ($this->getSColDefaultValue($sColName) == "♨")) {
                    if (!ctype_digit($sValue))
                        return false;
                    if (intval($sValue) < 0)
                        return false;
                }
                if ((substr_count($sValue, ',') > 0) || (substr_count($sValue, "'") > 0))
                    return false;
                preg_match("#^([\+\-]|)([0-9]*)(\.([0-9]*?)|)(0*)$#", trim($sValue), $o);
                $fVal = $o[1] . sprintf('%d', $o[2]) . ($o[3] != '.' ? $o[3] : '');
                return $fVal === $sValue;
                break;
            case "2":
                return (($sValue == "0") || ($sValue == "1"));
                break;
            case "3":
                if ($bIsDefaultValue && ($sValue == "♨"))
                    return true;
                if (strlen($sValue) != 8)
                    return false;
                if (!is_numeric(substr($sValue, 4, 2)))
                    return false;
                if (!is_numeric(substr($sValue, 6, 2)))
                    return false;
                if (!is_numeric(substr($sValue, 0, 4)))
                    return false;
                return checkdate(substr($sValue, 4, 2), substr($sValue, 6, 2), substr($sValue, 0, 4));
                break;
            case "4":
                if ($bIsDefaultValue && ($sValue == "♨"))
                    return true;
                if (strlen($sValue) != 15)
                    return false;
                if (!is_numeric(substr($sValue, 4, 2)))
                    return false;
                if (!is_numeric(substr($sValue, 6, 2)))
                    return false;
                if (!is_numeric(substr($sValue, 0, 4)))
                    return false;
                if (!is_numeric(substr($sValue, 9, 2)))
                    return false;
                if (!is_numeric(substr($sValue, 11, 2)))
                    return false;
                if (!is_numeric(substr($sValue, 13, 2)))
                    return false;
                if (substr($sValue, 8, 1) != "T")
                    return false;
                $dtt = substr($sValue, 0, 4) . "-" . substr($sValue, 4, 2) . "-" . substr($sValue, 6, 2) . " " . substr($sValue, 9, 2) .
                        ":" . substr($sValue, 11, 2) . ":" . substr($sValue, 13, 2);
                if (preg_match("/^(\d{4})-(\d{2})-(\d{2}) ([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/", $dtt, $matches)) {
                    if (checkdate($matches[2], $matches[3], $matches[1])) {
                        return true;
                    }
                }
                return false;
                break;
            case "4Z":
                if ($bIsDefaultValue && ($sValue == "♨"))
                    return true;
                if (strlen($sValue) != 16)
                    return false;
                if (!is_numeric(substr($sValue, 4, 2)))
                    return false;
                if (!is_numeric(substr($sValue, 6, 2)))
                    return false;
                if (!is_numeric(substr($sValue, 0, 4)))
                    return false;
                if (!is_numeric(substr($sValue, 9, 2)))
                    return false;
                if (!is_numeric(substr($sValue, 11, 2)))
                    return false;
                if (!is_numeric(substr($sValue, 13, 2)))
                    return false;
                if (substr($sValue, 8, 1) != "T")
                    return false;
                if (substr($sValue, 15, 1) != "Z")
                    return false;
                $dttZ = substr($sValue, 0, 4) . "-" . substr($sValue, 4, 2) . "-" . substr($sValue, 6, 2) . " " . substr($sValue, 9, 2) .
                        ":" . substr($sValue, 11, 2) . ":" . substr($sValue, 13, 2);
                if (preg_match("/^(\d{4})-(\d{2})-(\d{2}) ([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/", $dttZ, $matches)) {
                    if (checkdate($matches[2], $matches[3], $matches[1])) {
                        return true;
                    }
                }
                return false;
                break;
            case "5":
                if ($bIsDefaultValue && ($sValue == "♨"))
                    return true;
                if (strlen($sValue) != 6)
                    return false;
                if (!is_numeric(substr($sValue, 0, 2)))
                    return false;
                if (!is_numeric(substr($sValue, 2, 2)))
                    return false;
                if (!is_numeric(substr($sValue, 4, 2)))
                    return false;
                $tim = substr($sValue, 0, 2) . ":" . substr($sValue, 2, 2) . ":" . substr($sValue, 4, 2);
                if (preg_match("/^([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/", $tim, $matches)) {
                    return true;
                }
                return false;
                break;
            case "5Z":
                if ($bIsDefaultValue && ($sValue == "♨"))
                    return true;
                if (strlen($sValue) != 7)
                    return false;
                if (!is_numeric(substr($sValue, 0, 2)))
                    return false;
                if (!is_numeric(substr($sValue, 2, 2)))
                    return false;
                if (!is_numeric(substr($sValue, 4, 2)))
                    return false;
                if (substr($sValue, 6, 1) != "Z")
                    return false;
                $timZ = substr($sValue, 0, 2) . ":" . substr($sValue, 2, 2) . ":" . substr($sValue, 4, 2);
                if (preg_match("/^([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/", $timZ, $matches)) {
                    return true;
                }
                return false;
                break;
            case "6":
                return true;
                break;
            case "7":
                $aURLParts = explode(" ", $sValue);
                if (count($aURLParts) == 1)
                    return true;
                if (count($aURLParts) == 2)
                    return (($aURLParts[0] != $aURLParts[1]) && ($aURLParts[0] != "") && ($aURLParts[1] != ""));
                return (($aURLParts[1] != "") && ($aURLParts[count($aURLParts) - 1] != ""));
                break;
            case "8":
                return (($sValue == "0") || ($sValue == "1") || ($sValue == "2") || ($sValue == "3")
                        || ($sValue == "4") || ($sValue == "5") || ($sValue == "6") || ($sValue == "7")
                        || ($sValue == "8") || ($sValue == "9") || ($sValue == "10"));
                break;
        }
    }

    private function bColumnsAreUnique($aColumnsNames) {
        for ($i = 0; $i < $this->getINbRows(); $i++) {
            for ($j = $i + 1; $j < $this->getINbRows(); $j++) {
                $bConstraintViolated = false;
                foreach ($aColumnsNames as $sColName) {
                    if ($this->getCell($sColName, $i) != $this->getCell($sColName, $j)) {
                        $bConstraintViolated = false;
                        break 1;
                    } else {
                        $bConstraintViolated = true;
                    }
                }
                if ($bConstraintViolated) {
                    return false;
                }
            }
        }
        return true;
    }

    // PRINTING THE FILE INFO:

    public function info() {
        echo 'Version = <b>' . $this->getSVersion() . '</b><br><br>Encoding = <b>';
        switch ($this->getIEncoding()) {
            case 0:
                echo 'UTF8';
                break;
            case 1:
                echo 'UTF16 LE';
                break;
            case 2:
                echo 'UTF16 BE';
                break;
            case 3:
                echo 'UTF32 LE';
                break;
            case 4:
                echo 'UTF32 BE';
                break;
        }
        echo '</b><br><br>Number of columns = <b>' . $this->getINbColumns() . '</b><br><br>';
        echo 'Number of rows = <b>' . $this->getINbRows() . '</b><br><br>Values:<br>';
        // ---------------- VALUES TABLE -----------------
        echo '<table border="1" cellspacing="0" cellpadding="5"><tr>';
        foreach (array_keys($this->getAValues()) as $sColName) {
            echo '<td valign="top" style="background-color:#DEDEDE">' . $sColName . '</td>';
        }
        echo '</tr>';
        for ($j = 0; $j < $this->getINbRows(); $j++) {
            echo '<tr>';
            foreach (array_keys($this->getAValues()) as $sColName) {
                echo '<td valign="top">' . $this->getCell($sColName, $j) . '</td>';
            }
            echo '</tr>';
        }
        // -----------------------------------------------
        echo '</table><br>Columns properties:<br>';
        // ------------- COLS. PROP. TABLE --------------
        echo '<table border="1" cellspacing="0" cellpadding="5"><tr><td 
            valign="top" style="background-color:#DEDEDE"></td>';
        foreach (array_keys($this->getAValues()) as $sColName) {
            echo '<td valign="top" style="background-color:#DEDEDE">' . $sColName . '</td>';
        }
        echo '</tr><tr><td valign="top" style="background-color:#DEDEDE">Column type</td>';
        foreach (array_keys($this->getAValues()) as $sColName) {
            echo '<td valign="top">';
            $_aColType = $this->getAColType($sColName);
            echo '<b>' . $_aColType[0] . '</b>';
            switch ($_aColType[0]) {
                case "0":
                    echo ' (text)';
                    break;
                case "1":
                    if (count($_aColType) == 3) {
                        echo ' (stepper number (' . $_aColType[1] .
                        ',' . $_aColType[2] . '))';
                    } else {
                        echo ' (number)';
                    }
                    break;
                case "2":
                    echo ' (boolean)';
                    break;
                case "3":
                    echo ' (date)';
                    break;
                case "4":
                    echo ' (datetime)';
                    break;
                case "4Z":
                    echo ' (datetime UTC)';
                    break;
                case "5":
                    echo ' (time)';
                    break;
                case "5Z":
                    echo ' (time UTC)';
                    break;
                case "6":
                    echo ' (text enumeration with values: ';
                    for ($k = 2; $k < count($_aColType); $k++) {
                        if ($k > 2) {
                            echo', ';
                        }
                        echo "'" . $_aColType[$k] . "'";
                    }
                    echo ')';
                    break;
                case "7":
                    echo ' (web URL)';
                    break;
                case "8":
                    echo ' (rating)';
                    break;
                case "9":
                    echo ' (image)';
                    break;
                case "10":
                    echo ' (audio)';
                    break;
                case "11":
                    echo ' (video)';
                    break;
                case "12":
                    echo ' (PDF)';
                    break;
                case "13":
                    echo ' (file)';
                    break;
            }
            echo '</td>';
        }
        echo '</tr><tr><td valign="top" style="background-color:#DEDEDE">Column can be NULL</td>';
        foreach (array_keys($this->getAValues()) as $sColName) {
            echo '<td valign="top">' .
            (($this->getSColCanBeNULL($sColName) == "0") ? "no" : "yes")
            . '</td>';
        }
        echo '</tr><tr><td valign="top" style="background-color:#DEDEDE">Column is UNIQUE</td>';
        foreach (array_keys($this->getAValues()) as $sColName) {
            echo '<td valign="top">' .
            (($this->getSColIsUNIQUE($sColName) == "0") ? "no" : "yes")
            . '</td>';
        }
        echo '</tr><tr><td valign="top" style="background-color:#DEDEDE">Column default value</td>';
        foreach (array_keys($this->getAValues()) as $sColName) {
            $noteDefault = "";
            if (($_aColType[0] == "3") &&
                    ($this->getSColDefaultValue($sColName) == "♨")) {
                $noteDefault = " (today)";
            }
            if (($_aColType[0] == "4") &&
                    ($this->getSColDefaultValue($sColName) == "♨")) {
                $noteDefault = " (now)";
            }
            if (($_aColType[0] == "4Z") &&
                    ($this->getSColDefaultValue($sColName) == "♨")) {
                $noteDefault = " (now)";
            }
            if (($_aColType[0] == "5") &&
                    ($this->getSColDefaultValue($sColName) == "♨")) {
                $noteDefault = " (current time)";
            }
            if (($_aColType[0] == "5Z") &&
                    ($this->getSColDefaultValue($sColName) == "♨")) {
                $noteDefault = " (current time)";
            }
            if (($_aColType[0] == "1") &&
                    ($this->getSColDefaultValue($sColName) == "♨")) {
                $noteDefault = " (autonum)";
            }
            echo '<td valign="top">' . (($noteDefault != "") ? "<b>" : "") .
            $this->getSColDefaultValue($sColName) . (($noteDefault != "") ? "</b>" : "") .
            $noteDefault . '</td>';
        }
        echo '</tr><tr><td valign="top" style="background-color:#DEDEDE">Column width</td>';
        foreach (array_keys($this->getAValues()) as $sColName) {
            echo '<td valign="top">' . $this->getSColWidth($sColName) . '</td>';
        }
        echo '</tr><tr><td valign="top" style="background-color:#DEDEDE">Column sorting</td>';
        foreach (array_keys($this->getAValues()) as $sColName) {
            echo '<td valign="top">';
            $_aColSorting = $this->getAColSorting($sColName);
            if ($_aColSorting[0] != "0") {
                echo $_aColSorting[0] .
                (($_aColSorting[1] == "0") ? " (ASC)" : " (DESC)");
            }
            echo '</td>';
        }
        // -----------------------------------------------
        echo '</tr></table><br>Is the window maximized? = <b>' .
        (($this->getBWindowIsMaximized()) ? "yes" : "no") . '</b><br><br>';
        echo 'Window width = <b>' . $this->getIWindowWidth() . '</b><br><br>';
        echo 'Window height = <b>' . $this->getIWindowHeight() . '</b><br><br>';
        echo 'Row height = <b>' . $this->getIRowHeight() . '</b><br><br>';
        echo 'Comments = <b>' . $this->getSComments() . '</b><br><br>Keys:<br>';
        // ---------------- KEYS TABLE -----------------
        echo '<table border="1" cellspacing="0" cellpadding="5"><tr>';
        echo '<td valign="top" style="background-color:#DEDEDE">Key name</td>';
        echo '<td valign="top" style="background-color:#DEDEDE">Columns that affects</td></tr>';
        $_aKeys = $this->getAKeys();
        for ($j = 0; $j < count($_aKeys[0]); $j++) {
            echo '<tr><td valign="top">' . $_aKeys[0][$j] . '</td>';
            $sColsAffects = "";
            for ($k = 0; $k < count($_aKeys[1][$j]); $k++) {
                if ($k > 0) {
                    $sColsAffects.=', ';
                }
                $sColsAffects.=$_aKeys[1][$j][$k];
            }
            $sColsAffects.=".";
            echo '<td valign="top">' . $sColsAffects . '</td></tr>';
        }
        echo '</table><br>';
        echo 'Sync mode = <b>' . $this->getISyncMode() . ' (' . (($this->getISyncMode() == 0) ? "sync" : (($this->getISyncMode() == 1) ? "upload" : "download")) . ')</b><br><br>';
        echo 'Sync key = <b>' . $this->getSSyncKey() . '</b><br><br>';
        // ---------------------------------------------
    }

    // ###############################################################
}

class Aem {

    private static $AUDIO_EXTENSIONS = array("flac", "mp3", "wav", "aiff", "3gp", "m4p", "ogg", "wma", "m4a", "m4b");
    private static $VIDEO_EXTENSIONS = array("mp4", "mpg", "mpeg", "avi", "mov", "m4v");
    private static $IMAGE_EXTENSIONS = array("tif", "tiff", "gif", "jpeg", "jpg", "png", "bmp", "icns");
    private $ae2;
    private $iNbBytesMedia;
    private $iNbBytesIcon;
    private $sVersion;

    // GETTERS +++++++++++++++++++++

    public function getSFilePath() {
        return $this->ae2->getSFilePath();
    }

    public function getSVersion() {
        return $this->sVersion;
    }

    public function getSAe2Version() {
        return $this->ae2->getSVersion();
    }

    public function getIEncoding() {
        return $this->ae2->getIEncoding();
    }

    public function getINbRows() {
        return $this->ae2->getINbRows();
    }

    public function getINbColumns() {
        return $this->ae2->getINbColumns();
    }

    public function getAValues() {
        return $this->ae2->getAValues();
    }

    public function getCell($sColName, $iRowIndex) {
        $_aValues = $this->ae2->getAValues();
        if (isset($_aValues[$sColName][$iRowIndex])) {
            return $_aValues[$sColName][$iRowIndex];
        } else {
            return null;
        }
    }

    public function getAColType($sColName) {
        return $this->ae2->getAColType($sColName);
    }

    public function getSColCanBeNULL($sColName) {
        return $this->ae2->getSColCanBeNULL($sColName);
    }

    public function getSColIsUNIQUE($sColName) {
        return $this->ae2->getSColIsUNIQUE($sColName);
    }

    public function getSColDefaultValue($sColName) {
        return $this->ae2->getSColDefaultValue($sColName);
    }

    public function getSColWidth($sColName) {
        return $this->ae2->getSColWidth($sColName);
    }

    public function getBWindowIsMaximized() {
        return $this->ae2->getBWindowIsMaximized();
    }

    public function getIWindowWidth() {
        return $this->ae2->getIWindowWidth();
    }

    public function getIWindowHeight() {
        return $this->ae2->getIWindowHeight();
    }

    public function getIRowHeight() {
        return $this->ae2->getIRowHeight();
    }

    public function getAColSorting($sColName) {
        return $this->ae2->getAColSorting($sColName);
    }

    public function getSComments() {
        return $this->ae2->getSComments();
    }

    public function getAKeys() {
        return $this->ae2->getAKeys();
    }

    public function getISyncMode() {
        return $this->ae2->getISyncMode();
    }

    public function getSSyncKey() {
        return $this->ae2->getSSyncKey();
    }

    public function getINbBytesMedia() {
        return $this->iNbBytesMedia;
    }

    public function getINbBytesIcon() {
        return $this->iNbBytesIcon;
    }

    // +++++++++++++++++++++++++++++
    // SETTERS +++++++++++++++++++++

    public function setNbBytesHeaders($aephp, $IBm, $IBi) {
        if (get_class($aephp) != "AePHP") {
            throw new Exception('Use AePHP::parse to parse a file!');
        }
        $this->iNbBytesMedia = $IBm;
        $this->iNbBytesIcon = $IBi;
    }

    public function setSVersion($sPVersion) {
        $this->sVersion = $sPVersion;
    }

    public function setSFilePath($sFilePath) {
        $this->ae2->setSFilePath($sFilePath);
    }

    // +++++++++++++++++++++++++++++
    // REPLACE +++++++++++++++++++++
    public function replaceCell($sColName, $iRowIndex, $sNewValue) {
        $this->ae2->replaceCell($sColName, $iRowIndex, $sNewValue);
    }

    public function replaceDefaultValue($sColName, $sNewValue) {
        $this->ae2->replaceDefaultValue($sColName, $sNewValue);
    }

    public function increaseRows($increment = 1) {
        $this->ae2->increaseRows($increment);
    }

    public function decreaseRows($decrement = 1) {
        $this->ae2->decreaseRows($decrement);
    }

    // +++++++++++++++++++++++++++++

    public function exportIcon($sIconDestination) {
        if ($this->iNbBytesIcon > 0) {
            $aemFileHandle = fopen($this->getSFilePath(), "r");
            $iconFileHandle = fopen($sIconDestination . '/icon.png', 'w');
            fseek($aemFileHandle, 18 + $this->iNbBytesMedia);
            $aIconBytes = fread($aemFileHandle, $this->iNbBytesIcon);
            fwrite($iconFileHandle, $aIconBytes);
            fclose($iconFileHandle);
            fclose($aemFileHandle);
        }
    }

    public function exportMediaCell($sColName, $iRowIndex, $sMediaDestination, $sMemoryLimitMB = "", $iPBufferSize = 104857600, $sFileName = "") {
        $sMediaPointer = $this->getCell($sColName, $iRowIndex);
        $this->exportMediaPointer($sColName, $sMediaDestination, $sMemoryLimitMB, $iPBufferSize, $sFileName, $sMediaPointer);
    }

    public function exportMediaDefaultValue($sColName, $sMediaDestination, $sMemoryLimitMB = "", $iPBufferSize = 104857600, $sFileName = "") {
        $sMediaPointer = $this->getSColDefaultValue($sColName);
        $this->exportMediaPointer($sColName, $sMediaDestination, $sMemoryLimitMB, $iPBufferSize, $sFileName, $sMediaPointer);
    }

    private function exportMediaPointer($sColName, $sMediaDestination, $sMemoryLimitMB = "", $iPBufferSize = 104857600, $sFileName = "", $sMediaPointer) {
        if ($sMemoryLimitMB != "") {
            ini_set("memory_limit", $sMemoryLimitMB);
        }

        $iBufferSize = $iPBufferSize;
        $_aColType = $this->getAColType($sColName);
        $sColType = $_aColType[0];

        if (!$this->bIsValidConsolidatedMediaPointer($sMediaPointer, $sColType)) {
            throw new Exception('Invalid media pointer');
        }
        $aMediaPointer = explode(":", $sMediaPointer);
        $aemFileHandle = fopen($this->getSFilePath(), "r");
        if ($sFileName == "") {
            $sFileName = $aMediaPointer[2];
        }
        $mediaFileHandle = fopen($sMediaDestination . '/' . $sFileName . '.' . $aMediaPointer[3], 'w');
        $iMediaPointer = 18 + $aMediaPointer[0];
        $iBytesTransfered = 0;
        while ($iBytesTransfered < $aMediaPointer[1]) {
            if (($iBytesTransfered + $iBufferSize) > $aMediaPointer[1]) {
                $iNbBytesToTransfer = $aMediaPointer[1] - intval($iBytesTransfered);
            } else {
                $iNbBytesToTransfer = $iBufferSize;
            }
            fseek($aemFileHandle, $iMediaPointer);
            $aMediaBytes = fread($aemFileHandle, $iNbBytesToTransfer);
            fwrite($mediaFileHandle, $aMediaBytes);
            $iBytesTransfered += $iNbBytesToTransfer;
            $iMediaPointer += $iNbBytesToTransfer;
        }
        fclose($mediaFileHandle);
        fclose($aemFileHandle);
    }

    public function bIsValidConsolidatedMediaPointer($sMediaPointer, $sColType) {
        if ($sMediaPointer == "")
            return false;
        $cFirstChar = substr($sMediaPointer, 0, 1);
        if ($cFirstChar == "~")
            return false;
        if ($cFirstChar == "@")
            return false;
        if ($cFirstChar == "&")
            return false;
        if (!isset($this->iNbBytesMedia)) {
            return false;
        }

        $aMediaPointer = explode(":", $sMediaPointer);

        if (count($aMediaPointer) != 4)
            return false;
        if (!ctype_digit($aMediaPointer[0]))
            return false;
        if (!ctype_digit($aMediaPointer[1]))
            return false;
        if (!($aMediaPointer[0] >= 0))
            return false;
        if (!($aMediaPointer[1] > 0))
            return false;
        if (($aMediaPointer[0] + $aMediaPointer[1]) > $this->iNbBytesMedia)
            return false;
        if ($aMediaPointer[3] == "")
            return false;
        if (substr_count($aMediaPointer[3], ' ') > 0)
            return false;
        if (($sColType == "9") && !(in_array(strtolower($aMediaPointer[3]), self::$IMAGE_EXTENSIONS)))
            return false;
        if (($sColType == "10") && !(in_array(strtolower($aMediaPointer[3]), self::$AUDIO_EXTENSIONS)))
            return false;
        if (($sColType == "11") && !(in_array(strtolower($aMediaPointer[3]), self::$VIDEO_EXTENSIONS)))
            return false;
        if (($sColType == "12") && !(strtolower($aMediaPointer[3] == "pdf")))
            return false;

        return true;
    }

    public function __construct($aephp, $aBytesVERSION_ENCODING, $aBytes, $bParseWithThrows) {
        if (get_class($aephp) != "AePHP") {
            throw new Exception('Use AePHP::parse to parse a file!');
        }
        $this->ae2 = new Ae2($aephp, $aBytesVERSION_ENCODING, $aBytes, $bParseWithThrows);
    }

    public function removeAllRows() {
        $this->ae2->removeAllRows();
    }

    public function info() {
        $this->ae2->info();
        echo 'Number of media bytes = <b>' . $this->getINbBytesMedia() . '</b><br><br>';
        echo 'Number of icon bytes = <b>' . $this->getINbBytesIcon() . '</b><br><br>';
    }

    public function bUNIQUEConstraintsAreRespected() {
        return $this->ae2->bUNIQUEConstraintsAreRespected();
    }

    public function bKeysAreRespected() {
        return $this->ae2->bKeysAreRespected();
    }

    public function bDefaultValuesAndCellValuesAreValid() {
        // check cell values sintax and EMPTY constraint:
        foreach (array_keys($this->getAValues()) as $sColName) {
            for ($i = 0; $i < $this->getINbRows(); $i++) {
                if (!$this->bCellDefaultValueIsValid($sColName, false, $i)) {
                    return 'error in column "' . $sColName . '" / cell "' . $i . 
                            '", invalid cell value: "'.$this->getCell($sColName, $i).'"';
                }
            }
            if (!$this->bCellDefaultValueIsValid($sColName, true)) {
                return 'error in column "' . $sColName . '", invalid default value';
            }
        }
        return true;
    }

    public function bCellDefaultValueIsValid($sColName, $bIsDefaultValue, $iRowIndex = "") {
        $_aColType = $this->getAColType($sColName);
        $sColType = $_aColType[0];
        if (!(($sColType == "9") || ($sColType == "10") || ($sColType == "11") || ($sColType == "12") ||
                ($sColType == "13"))) {
            return $this->ae2->bCellDefaultValueIsValid($sColName, $bIsDefaultValue, $iRowIndex);
        } else {
            if ($bIsDefaultValue) {
                $sValue = $this->getSColDefaultValue($sColName);
            } else {
                $sValue = $this->getCell($sColName, $iRowIndex);
                if (($this->getSColCanBeNULL($sColName) == "0") && ($sValue == "")) {
                    return false;
                }
            }
            if ($sValue == "")
                return true;
            $cFirstChar = substr($sValue, 0, 1);
            if (($cFirstChar == "&") || ($cFirstChar == "@") || ($cFirstChar == "~")) {
                return false;
            }
            return $this->bIsValidConsolidatedMediaPointer($sValue, $sColType);
        }
    }

}

?>