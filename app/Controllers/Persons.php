<?php

namespace App\Controllers;

use App\Models\Person;
use Config\Services;
use function Tamtamchik\NameCase\str_name_case;

abstract class Persons extends Secure_Controller
{
    protected Person $person;

    /**
     * @param string|null $module_id
     */
    public function __construct(?string $module_id = null)
    {
        parent::__construct($module_id);

        $this->person = model(Person::class);
    }

    /**
     * @return void
     */
    public function getIndex(): void
    {
        $data['table_headers'] = get_people_manage_table_headers();

        echo view('people/manage', $data);
    }

    /**
     * Gives search suggestions based on what is being searched for
     */
    public function getSuggest(): void
    {
        $search = $this->request->getGet('term');
        $suggestions = $this->person->get_search_suggestions($search);

        echo json_encode($suggestions);
    }

    /**
     * Gets one row for a person manage table. This is called using AJAX to update one row.
     */
    public function getRow(int $row_id): void
    {
        $data_row = get_person_data_row($this->person->get_info($row_id));

        echo json_encode($data_row);
    }

    /**
     * Capitalize segments of a name, and put the rest into lower case.
     * You can pass the characters you want to use as delimiters as exceptions.
     * The function supports UTF-8 strings
     *
     * Example:
     * i.e. <?php echo nameize("john o'grady-smith"); ?>
     *
     * returns John O'Grady-Smith
     */
    protected function nameize(string $input): string
    {
        $adjusted_name = str_name_case($input);

        // TODO: Use preg_replace to match HTML entities and convert them to lowercase. This is a workaround for https://github.com/tamtamchik/namecase/issues/20
        return preg_replace_callback('/&[a-zA-Z0-9#]+;/', function ($matches) {
            return strtolower($matches[0]);
        }, $adjusted_name);
    }
}
