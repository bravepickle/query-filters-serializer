<?php
namespace Helper;
use Filter\ParsingException;

/**
 * Formatter of input values.
 * @author victor
 *
 */
class Formatter
{
    /**
     * Group 2-dimensional array by selected keys
     * If keys are not unique and $bAllowMultipleRowsPerGroup not set then vals will be overwritten
     *
     * @param array $arrGroupKeys array of keys by which it should be grouped. Order is important
     * @param array $arrInput input array to format
     * @param bool $bAllowMultipleRowsPerGroup
     * @return array
     * @throws \Filter\ParsingException
     */
    public static function groupArray(array $arrGroupKeys, array $arrInput, $bAllowMultipleRowsPerGroup = true)
    {
        if (empty($arrInput)) return array();

        $arrRow = reset($arrInput);
        $arrNotFound = array_diff($arrGroupKeys, array_keys($arrRow));
        if ($arrNotFound) {
            throw new ParsingException('Grouping keys are not defined: ' . implode(', ', $arrNotFound));
        }

        $sAdd = $bAllowMultipleRowsPerGroup ? '[]' : '';

        $arrRes = array();
        foreach ($arrInput as $arrRow) {
            $sSetter = '$arrRes';
            foreach ($arrGroupKeys as $sKey) {
                $sSetter .= "['" . $arrRow[$sKey] . "']";
            }
            $sSetter .= $sAdd . ' = $arrRow;';

            eval($sSetter); // TODO: replace it os that eval won't be used
        }

        return $arrRes;
    }
}

