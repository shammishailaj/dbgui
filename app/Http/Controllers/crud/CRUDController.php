<?php

namespace App\Http\Controllers\crud;

use App\Http\Controllers\crud\PageController;
use App\Table;
use DB;
use Validator;
use Illuminate\Http\Request;
use App\Helpers\Settings;

class CRUDController extends PageController
{
    /**
     * Values  for paginations  -  rows  per  page
     *
     * @var array
     */
    const PAGINATION_ARRAY = [10, 20, 30, 40, 50, 60, 70, 80, 90 ,100];

    /**
     * Flaged value  for clear filters
     *
     * @var array
     */
    const FILTER_CLEAR = 'clear';

    /**
     * Current value  for  rows  per  page
     *
     * @global private
     * @var integer
     */
    private $currentPagination = 10;

    /**
     * Default field for  sorting
     *
     * @global private
     * @var string
     */
    private $sortingField = 'id';

    /**
     * Default direct for sorting ASC|DESC
     *
     * @global private
     * @var string
     */
    private $sortingDirection = 'DESC';

    /**
     * Array  with validaton rules
     *
     * @global public
     * @var array
     */
    public $validateArray =  [];

    /**
     * Locked array - using fields
     *
     * @global public
     * @var array
     */
    public $systemColumns = ['id', 'linked_data_id'];

    /**
     * Controller for index page
     *
     * @return Response
     */
    public function dashboard()
    {
        return $this->view('crud.pages.index', $this->Data);
    }

    /**
     * Controller for crud/{table_code} - items  list
     *
     * @param  string $tableCode  table code
     * @return Response
     */
    public function itemsList ($tableCode)
    {
        //  Checking table
        $this->setTable($tableCode, 'fieldsView');

        // init row model
        $this->Data['item'] = null;

        // Add page number  to  session
        if (request()->has('page')) {
            request()->session()->put('page.'.$this->Data['table']->code, request()->input('page'));
        }

        // Copy page number  from session to   \Illuminate\Http\Request
        if (request()->session()->has('page.'.$this->Data['table']->code)) {
            request()->request->add(['page' => request()->session()->get('page.'.$this->Data['table']->code)]);
        }

        //  Set num  rows
        if (request()->session()->has('numrows.'.$this->Data['table']->code)) {
            $this->currentPagination =  request()->session()->get('numrows.'.$this->Data['table']->code);
        }

        // Set fields
        $selectedArray = $this->Data['table']->fieldsView->pluck('code')->toArray();

        // Add 'id' element to array
        array_unshift($selectedArray, 'id');

        // Creating SQL request
        // Main SQL selected fields
        $this->Data['items'] = DB::table($this->Data['table']->code)->select($selectedArray);

        // Apply settings
        if ($this->Data['table']->code == 'fields' && !Settings::get('dev_mode_tables')) {
            $tableIdx = Table::where('flag_system', 0)->get()->pluck('id');
            $this->Data['items'] = $this->Data['items']->whereIn('table_id', $tableIdx);
        }

        // Apply settings
        if ($this->Data['table']->code == 'tables' && !Settings::get('dev_mode_tables')) {
            $this->Data['items'] = $this->Data['items']->where('flag_system', 0);
        }

        // Apply  filters
        if (request()->session()->has('filters.'.$this->Data['table']->code)) {
            $filters  = request()->session()->get('filters.'.$this->Data['table']->code);

            foreach($filters as $filter) {
                if ($fieldData = $this->getTableFieldById($filter['field'])) {
                    $this->Data['items'] = $this->{$this->fieldClass($fieldData)}->setFilterWhere($this->Data['items'], $fieldData,  $filter['value']);
                }
            }
        }

        // Set Sorting value  to  session
        if (request()->session()->has('sorting.'.$this->Data['table']->code)) {
            $sort = request()->session()->get('sorting.'.$this->Data['table']->code);
            $this->sortingField = $sort['field']->code;
            $this->sortingDirection = $sort['direction'];
        }

        // Apply sorting
        $this->Data['items']  = $this->Data['items']->orderBy($this->sortingField, $this->sortingDirection);

        // Run SQL QUERY
        $this->Data['items']  = $this->Data['items']->paginate($this->currentPagination);

        // Mutate rows  by  Fields Classes
        foreach ($this->Data['items'] as $key => $value) {
            foreach ($this->Data['table']->fieldsView as $field) {
                $this->Data['items'][$key]->{$field->code} = $this->{$this->fieldClass($field)}->mutateList($this->Data['items'][$key]->{$field->code}, $field);
            }
        }

        // If $this->Data['items'] is empty -  clear current  pagination  and redirect to pagination first page
        if ($this->Data ['items']->count() == 0 && request()->session()->has('page.'.$this->Data['table']->code)) {
            $this->forgetPagination();
            return redirect(route('items.list', $tableCode));
        }

        // Mutate  filters  by  Fields Classes
        foreach ($this->Data['table']->filters as $key => $filter) {
            $this->Data['table']->filters[$key] = $this->{$this->fieldClass($filter)}->setFilterOptions($filter, $this->Data['table']);
        }

        // Set View's variables
        $this->Data['paginationArray'] = self::PAGINATION_ARRAY;
        $this->Data['currentPagination'] = $this->currentPagination;
        $this->Data['sortingField'] = $this->sortingField;
        $this->Data['sortingDirection'] = $this->sortingDirection;

        if ($this->Data['sortingDirection'] == 'ASC') {
            $this->Data['direction'] = 'desc';
        } else {
            $this->Data['direction'] = 'asc';
        }

        return $this->view('crud.pages.list', $this->Data);
    }

    /**
     * Controller for crud/{table}/list/filter/{field}/value/{value} - items  list with filter
     *
     * @param string  $tableCode table code
     * @param string  $fieldID filter ID
     * @param string  $value filter value
     * @return CRUDController
     */
    public function itemsListFilter ($tableCode, $fieldID, $value)
    {
        //  Checking table
        $this->setTable($tableCode);

        // Get  table  field by $fieldID
        if ($field = $this->getTableFieldById($fieldID)) {
            if ($value == self::FILTER_CLEAR) {
                // Delete filter from session
                request()->session()->forget('filters.'.$this->Data['table']->code.'.'.$fieldID);
            } else {
                //  Add filter into session
                request()->session()->put('filters.'.$this->Data['table']->code.'.'.$fieldID, ['field' =>  $fieldID, 'value' => $value]);
            }
        }

        // Clear  pagination if  apply  new filter
        $this->forgetPagination();

        return redirect (route('items.list', $tableCode));
    }

    /**
     * Controller for crud/{table}/list/sort/{field}/direction/{value} - items  list with sorting
     *
     * @param string  $tableCode table code
     * @param string  $fieldID sorted field ID
     * @param string  $value sort value  ASC|DESC
     * @return CRUDController
     */
    public function itemsListSorting  ($tableCode, $fieldID, $value)
    {
        //  Checking table
        $this->setTable($tableCode);

        // Get  table  field by $fieldID
        if ($field = $this->getTableFieldById($fieldID)) {
            $direction = ($value == 'desc') ? 'DESC' : 'ASC';

            // Save  sorting  in  session
            request()->session()->put('sorting.'.$this->Data['table']->code, ['field' =>  $field, 'direction' => $direction]);
        }

        return redirect (route('items.list', $tableCode));
    }

    /**
     * Controller for crud/{table}/numrows/{value}:   set num rows  on page
     *
     * @param string  $tableCode table code
     * @param integer  $numRowValue num rows
     * @return Responce
     */
    public function itemsListNumRows ($tableCode, $numRowValue)
    {
        //  Checking table
        $this->setTable($tableCode);

        if(in_array($numRowValue, self::PAGINATION_ARRAY)) {
            request()->session()->put('numrows.'.$this->Data['table']->code, $numRowValue);
        }

        return redirect(route('items.list', $tableCode));
    }

    /**
     * Controller for crud/{table}/flag/{field}/id/{id} - change  value of  flag
     *
     * @param string  $tableCode table code
     * @param string  $fieldID field ID
     * @param string  $id row  ID
     * @return CRUDController
     */
    public function itemsListFlag ($tableCode, $fieldID, $id)
    {
        //  Checking table
        $this->setTable($tableCode);

        // Get  table  field by $fieldID
        if ($field = $this->getTableFieldById($fieldID)) {
            // Get row
            $row = DB::table($this->Data['table']->code)->where('id', $id)->first();

            if ($row)  {
                // Check value of flag
                if ($row->{$field->code}) {
                    //  Set  flag field  to 0
                    $row = DB::table($this->Data['table']->code)->where('id', $id)->update([$field->code => 0]);
                } else {
                    //  Set  flag field  to 1
                    $row = DB::table($this->Data['table']->code)->where('id', $id)->update([$field->code => 1]);
                }
            }
        }

        return redirect (route('items.list', $tableCode));
    }

    /**
     * Controller for crud/{table_code}/add GET show  form
     *
     * @param string  $tableCode table code
     * @return Response
     */
    public function itemAddGet ($tableCode)
    {
        // Check table
        $this->setTable($tableCode);

        // Init variables
        $this->Data['mode'] = 'add';

        // Mutate fields
        foreach ($this->Data['table']->fields as $key => $field) {
            $this->Data['table']->fields[$key] = $this->{$this->fieldClass($field)}->mutateAddGet($field);
        }

        return $this->view('crud.pages.form', $this->Data);
    }

    /**
     * Controller for crud/{table_code}/add POST process  POST  form
     *
     * @param  \Illuminate\Http\Request $request
     * @param string  $tableCode table code
     * @return Response
     */
    public function itemAddPost (Request $request, $tableCode)
    {
        // Check table and row
        $this->setTable($tableCode);

        // Validate
        $this->createValidateArray($this->Data['table']->fields, 'add');
        $validator = Validator::make($request->all(), $this->validateArray);

        // return form if  error
        if ($validator->fails()) {
            return redirect(route('items.add', $tableCode))
                ->withErrors($validator)
                ->withInput();
        }

        // Inserting array for database
        $insertArray = [];

        // Mutate POST data	 By Fields Classes
        foreach ($this->Data['table']->fields as $key => $field) {
            // Mutate  insertArray adding new  keys with values from request (POST)
            $insertArray = $this->{$this->fieldClass($field)}->mutateAddPost($request, $field, $insertArray);
        }

        //  Insert row
        DB::table($this->Data['table']->code)->insert($insertArray);

        // Call extra func.
        $this->itemAddPostMutate($insertArray);

        // Redirect  to items list
        return redirect(route('items.list', $tableCode));
    }

    /**
     * Controller for crud/{table_code}/edit/{id} GET:  show  edit form
     *
     * @param string  $tableCode table code
     * @param integer  $id row ID
     * @return Response
     */
    public function itemEditGet ($tableCode, $id)
    {
        // Check table  by  code
        $this->setTable($tableCode, 'fieldsEdit');

        // Check row by Id
        $this->checkItem($id);

        // Init variables
        $this->Data['mode'] = 'edit';

        // Mutate fields
        foreach ($this->Data['table']->fieldsEdit as $key => $field) {
            $this->Data['table']->fieldsEdit[$key] = $this->{$this->fieldClass($field)}->mutateEditGet($field, $this->Data['item']);
        }

        return $this->view('crud.pages.form', $this->Data);
    }

    /**
     * Controller for crud/{table_code}/edit/{id} POST:  proccess edit form
     *
     * @param  \Illuminate\Http\Request $request
     * @param string  $tableCode table code
     * @param integer  $id row ID
     * @return Response
     */
    public function itemEditPost (Request $request, $tableCode, $id)
    {
        // Check table  by  code
        $this->setTable($tableCode, 'fieldsEdit');

        // Check row  by  ID
        $this->checkItem($id);

        // Validate
        $this->createValidateArray($this->Data['table']->fieldsEdit, 'edit');
        $validator = Validator::make($request->all(), $this->validateArray);

        // return form
        if ($validator->fails()) {
            return redirect(route('items.edit', [$tableCode, $id]))
                ->withErrors($validator)
                ->withInput();
        }

        // Mutate POST data
        $updateArray = [];
        foreach ($this->Data['table']->fieldsEdit as $key => $field) {
            $updateArray = $this->{$this->fieldClass($field)}->mutateEditPost($request, $field, $updateArray);
        }

        // Update  row
        DB::table($this->Data['table']->code)->where('id', $id)->update($updateArray);

        // Extra hadler
        $this->itemEditPostMutate($updateArray);

        return redirect(route('items.list', $tableCode));
    }

    /**
     * Controller for crud/{table}/delete/{id} GET: delete row
     *
     * @param string  $tableCode table code
     * @param integer  $id row ID
     * @return CRUDController
     */
    public function itemDelete ($tableCode, $id)
    {
        // Check table  by  code
        $this->setTable($tableCode);

        // Check row  by  ID
        $this->checkItem($id);

        // Mutate row
        foreach ($this->Data['table']->fields as $key => $field) {
            $this->{$this->fieldClass($field)}->mutateDelete($this->Data['item'], $field);
        }

        // Delete row
        DB::table($this->Data['table']->code)->where('id', $id)->delete();

        // Extra func after delete row
        $this->itemDeleteMutate();

        return redirect (route('items.list', $tableCode));
    }

    /**
     * Return  table field  by  ID
     *
     * @param int  $fieldId table field ID
     * @return array
     */
    protected function getTableFieldById ($id)
    {
        foreach ($this->Data['table']->fields as $key => $field) {
            if ($field->id == $id) {
                return $field;
            }
        }

        return false;
    }

    /**
     * Set current  table by table code
     *
     * @param string  $tableCode table code  (SLUG)
     * @return boolean
     */
    protected function setTable($tableCode, $relations = 'fields')
    {
        // Table model
        $tableModel = Table::with($relations)->where('url', $tableCode)->first();

        // Check permissions
        if ($tableModel) {
            if (!Settings::get('dev_mode_tables')) {
                if (!$tableModel->flag_view) {
                    abort(403);
                }
            }
            $this->Data['table'] = $tableModel;
        } else {
            abort(404);
        }
    }

    /**
     * Set current  row by row ID
     *
     * @param int  $id row ID
     * @return boolean
     */
    protected function checkItem($id)
    {
        // Check row
        $rowModel = DB::table($this->Data['table']->code)->where('id', $id)->first();

        if ($rowModel) {
            $this->Data['item'] = $rowModel;
        } else {
            abort(404);
        }
    }

    /**
     * Create array with validate rules
     *
     * @param array  $fields table  fields  array
     * @return array
     */
    protected function createValidateArray ($fields, $mode)
    {
        foreach ($fields as $field) {
            // implement require property
            if ($field->flag_required) {
                $this->validateArray [$field->code][] = 'required';
            } else {
                $this->validateArray [$field->code][] = 'nullable';
            }

            // implement unique property
            if ($field->flag_unique) {
                //
                $uniqueRule = 'unique:'.$this->Data['table']->code.','.$field->code;
                if (isset($this->Data['item']) && $this->Data['item']) $uniqueRule .= ','.$this->Data['item']->id;

                $this->validateArray [$field->code][] = $uniqueRule;
            }

            // Get validate from fields classes
            $this->validateArray [$field->code] = $this->{$this->fieldClass($field)}->getValidate($this->validateArray [$field->code], $field, $mode);
        }

    }

    /**
     * Forget pagination
     *
     * @return void
     */
    protected function forgetPagination ()
    {
        request()->session()->forget('page.'.$this->Data['table']->code);
    }

    /**
     * Action after insert row data
     *
     * @param  array $insertArray
     * @return void
     */
    protected function itemAddPostMutate ($insertArray)
    {
        //
    }

    /**
     * Action after update row data
     *
     * @param array  $oldData row data before update
     * @param array  $updatedData row data after update
     * @return void
     */
    protected function itemEditPostMutate ($oldData)
    {
        //
    }

    /**
     * Action after delete row data
     *
     * @param array  $rowData deleting row data
     * @return void
     */
    protected function itemDeleteMutate ()
    {
        //
    }
}
