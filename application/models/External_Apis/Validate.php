<?php

class Validate extends CI_Model
{

    public function validation($fields)
    {

        if (!is_array($fields)) {
            return false;
        } else {
            $validatin_error_message = array();
            $response = array();
            foreach ($fields as $key => $value) {
                $removeFlag = false;
                $field_name = $value['field'];
                $field_value = $value['value'];
                $conditions = explode('|', $value['condition']);
                foreach ($conditions as $condition) {
                    if (in_array($field_name, $validatin_error_message)) {
                        continue;
                    }
                    $strsplit = str_split($condition);
                    if ($strsplit[0] == '{') {
                        $condition = str_replace('{', "", $condition);
                        $condition = str_replace('}', "", $condition);
                        $condition = explode(":", $condition);
                    }

                    if (is_array($condition)) {
                        switch ($condition[0]) {
                            case 'length':
                                if (strlen($field_value) != $condition[1]) {
                                    array_push($response, ' Invalid ' . $field_name . '.');
                                    array_push($validatin_error_message, $field_name);
                                }
                                break;
                            case 'position':
                                $splitValue = str_split($field_value);
                                if (strpos($condition[2], ',') !== false) {
                                    $validation_value = explode(',', $condition[2]);
                                } else {
                                    $validation_value = $condition[2];
                                }
                                if (is_array($validation_value)) {
                                    if (!in_array($splitValue[$condition[1]], $validation_value)) {
                                        array_push($response, 'Invalid ' . $field_name);
                                        array_push($validatin_error_message, $field_name);
                                    }
                                } else {
                                    if ($splitValue[$condition[1]] != $validation_value) {
                                        array_push($response, 'Invalid ' . $field_name);
                                        array_push($validatin_error_message, $field_name);
                                    }
                                }
                                break;
                            case 'unique':
                                $table_name = $condition[1];
                                $column_name = $condition[2];
                                if (isset($condition[3]) && isset($condition[4])) {
                                    if ($condition[3] != '' && $condition[4] != '') {
                                        $this->db->where($condition[3], $condition[4]);
                                    }
                                }
                                $checkExists = $this->db->select($column_name)
                                    ->from($table_name)
                                    ->where($column_name, $field_value)->get();
                                if ($checkExists->num_rows() > 0) {
                                    array_push($response, $field_name . ' already exists! Please enter another ' . $field_name);
                                    array_push($validatin_error_message, $field_name);
                                }
                                break;
                            case 'exist':
                                $table_name = $condition[1];
                                $column_name = $condition[2];

                                if (count($condition) > 3) {
                                    $I = 0;
                                    $J = 0;
                                    for ($i = 3; $i < count($condition); $i = $i + 2) {
                                        if (isset($condition[3 + $I]) && isset($condition[4 + $J])) {
                                            if ($condition[3 + $I] != '' && $condition[4 + $J] != '') {
                                                $this->db->where($condition[3 + $I], $condition[4 + $J]);
                                            }
                                        } else {
                                            continue;
                                        }
                                        $I = $I + 2;
                                        $J = $J + 2;
                                    }
                                }


                                $checkExists = $this->db->select($column_name)
                                    ->from($table_name)
                                    ->where($column_name, $field_value)->get();


                                if ($checkExists->num_rows() == 0) {
                                    array_push($response, 'Not Exist ' . $field_name);
                                    array_push($validatin_error_message, $field_name);
                                }
                                break;
                            case 'possibility':
                                $values = explode(',', $condition[1]);
                                if (is_array($values)) {
                                    if (!in_array(strtolower($field_value), $values)) {
                                        array_push($response, 'Invalid ' . $field_name);
                                        array_push($validatin_error_message, $field_name);
                                    }
                                } else {
                                    if ($field_value == $values) {
                                        array_push($response, 'Invalid ' . $field_name);
                                        array_push($validatin_error_message, $field_name);
                                    }
                                }
                                break;
                            case 'range':
                                list($min_range, $max_range) = explode('-', $condition[1]);
                                if ($min_range > $field_value || $max_range < $field_value) {
                                    array_push($response, 'Invalid ' . $field_name);
                                    array_push($validatin_error_message, $field_name);
                                }
                                break;
                            case 'max':
                                $max_value = $condition[1];
                                if ($field_value > $max_value) {
                                    array_push($response, $field_value . ' cannot exceed ' . $max_value);
                                    array_push($validatin_error_message, $field_name);
                                }
                                break;
                            case 'min':
                                $min_value = $condition[1];
                                if ($field_value < $min_value) {
                                    array_push($response, $field_value . ' should be greater than ' . $min_value);
                                    array_push($validatin_error_message, $field_name);
                                }
                                break;
                            case 'max-length':
                                $max_length = $condition[1];
                                if (strlen($field_value) > $max_length) {
                                    array_push($response, 'Invalid ' . $field_name);
                                    array_push($validatin_error_message, $field_name);
                                }
                                break;
                            case 'min-length':
                                $min_length = $condition[1];
                                if (strlen($field_value) < $min_length) {
                                    array_push($response, 'Invalid ' . $field_name);
                                    array_push($validatin_error_message, $field_name);
                                }
                                break;
                            case 'min-salary-length':
                                $min_length = $condition[1];
                                if (strlen($field_value) < $min_length) {
                                    array_push($response, 'Please enter your Net Monthly Income in full.');
                                    array_push($validatin_error_message, $field_name);
                                }
                                break;
                        }
                    } else {
                        switch ($condition) {
                            case 'required':
                                if (empty(trim($field_value)) && $field_value == '') {
                                    $removeFlag = true;
                                    array_push($response, $field_name . ' cannot be empty');
                                    array_push($validatin_error_message, $field_name);
                                }
                                break;
                            case 'alpha':
                                if (!ctype_alpha($field_value)) {
                                    array_push($response, $field_name . ' should contain alphabets only.');
                                    array_push($validatin_error_message, $field_name);
                                }
                                break;
                            case 'number':
                                if (!is_numeric($field_value)) {
                                    array_push($response, $field_name . ' should contain numbers only.');
                                    array_push($validatin_error_message, $field_name);
                                }
                                break;
                            case 'optional':
                                if (empty($field_value)) {
                                    $removeFlag = true;
                                }
                                break;
                            case 'email':
                                if (filter_var($field_value, FILTER_VALIDATE_EMAIL) === false) {
                                    array_push($response, "Invalid " . $field_name);
                                    array_push($validatin_error_message, $field_name);
                                } else {
                                    $domain_name = explode('@', $field_value)[1];
                                    if (checkdnsrr($domain_name, 'MX') === false) {
                                        array_push($response, "Invalid " . $field_name);
                                        array_push($validatin_error_message, $field_name);
                                    }
                                }
                                break;
                            case 'date':
                                $date = DateTime::createFromFormat('Y-m-d', $field_value);
                                $date_errors = DateTime::getLastErrors();
                                if (($date_errors['warning_count'] + $date_errors['error_count']) > 0) {
                                    array_push($response, 'Invalid ' . $field_name);
                                    array_push($validatin_error_message, $field_name);
                                }
                                break;
                            case 'dob':
                                $current_years = date('Y');
                                $yob = date('Y', strtotime($field_value));
                                $age = $current_years - $yob;
                                if ($age < 18) {
                                    array_push($response, 'Minimum age should be 18 years.');
                                } else if ($age > 65) {
                                    array_push($response, 'Maximum age should be 65 years.');
                                }
                                break;
                            case 'alpha_space':
                                $expression = '/[^a-z\s]/i';
                                $non_match_count = preg_match_all($expression, $field_value);
                                if ($non_match_count > 0) {
                                    array_push($response, 'Invalid ' . $field_name);
                                    array_push($validatin_error_message, $field_name);
                                }
                                break;
                            case 'float':
                                if (!filter_var($field_value, FILTER_VALIDATE_FLOAT)) {
                                    array_push($response, $field_name . ' not valid.');
                                    array_push($validatin_error_message, $field_name);
                                }
                                break;
                        }
                    }

                    if ($removeFlag === true) {
                        unset($fields[$key]);
                        break;
                    }
                }
                // print_r($validatin_error_message);
                // $validatin_error_message;
            }
            if (count(array_filter($response)) > 0) {
                return $response;
            } else {
                return true;
            }
        }
    }
}
