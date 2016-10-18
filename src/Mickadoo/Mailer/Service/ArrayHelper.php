<?php

namespace Mickadoo\Mailer\Service;

class ArrayHelper
{
    /**
     * @param array $array
     * @param string $prefix
     * @return array
     */
    public function flatten(array $array, $prefix = '')
    {
        $result = array();

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = $result + $this->flatten($value, $prefix . $key . '.');

            } else {
                $result[$prefix . $key] = $value;
            }
        }

        return $result;
    }

    /**
     * @param array $array
     * @param string $decoration
     * @return array
     */
    public function decorateKeys(array $array, $decoration = '%')
    {
        $arrayDecorated = [];

        foreach ($array as $key => $value) {
            $arrayDecorated[$decoration . $key . $decoration] = $value;
        }

        return $arrayDecorated;
    }
}
