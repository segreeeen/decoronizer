<?php
/**
 * Created by PhpStorm.
 * User: Chef
 * Date: 2020-04-21
 * Time: 16:25
 */

namespace LocalizationDataBuilder\Business;


interface JsonHelperInterface
{
    /**
     * @param array $dataToEncode
     *
     * @return string
     */
    public function build_json (array $dataToEncode): string;
}