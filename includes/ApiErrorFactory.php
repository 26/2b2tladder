<?php

namespace Api;

class ApiErrorFactory
{
    /**
     * @param $invalid_parameter
     * @return array
     */
    public function createInvalidParameterResult($invalid_parameter) {
        return [
            "error" => [
                "code" => "invalid_parameter",
                "info" => "Unrecognized value for parameter '$invalid_parameter'"
            ]
        ];
    }

    /**
     * @param $missing_parameter
     * @return array
     */
    public function createMissingParameterResult($missing_parameter) {
        return [
            "error" => [
                "code" => "missing_parameter",
                "info" => "The required parameter '$missing_parameter' was not supplied"
            ]
        ];
    }
}