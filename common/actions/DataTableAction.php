<?php

namespace common\actions;

use nullref\datatable\DataTableAction as BaseDataTableAction;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Response;

class DataTableAction extends BaseDataTableAction
{
    /**
     * Add extra fields to dataset
     * These fields could be used at render function
     *
     * @var array
     */
    public $extraColumns = [];

    /**
     * @return array|ActiveRecord[]
     */
    public function run()
    {
        /** @var ActiveQuery $originalQuery */
        $originalQuery = $this->query;
        $filterQuery = clone $originalQuery;
        $draw = $this->getParam('draw');
        $filterQuery->where = null;
        $search = $this->getParam('search', ['value' => null, 'regex' => false]);
        $columns = $this->getParam('columns', []);
        $order = $this->getParam('order', []);
        $filterQuery = $this->applyFilter($filterQuery, $columns, $search);
        $filterQuery = $this->applyOrder($filterQuery, $columns, $order);
        if (!empty($originalQuery->where)) {
            $filterQuery->andWhere($originalQuery->where);
        }
        $filterQuery
            ->offset($this->getParam('start', 0))
            ->limit($this->getParam('length', -1));
        $dataProvider = new ActiveDataProvider(['query' => $filterQuery, 'pagination' => ['pageSize' => Yii::$app->request->getQueryParam('length', 10)]]);
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            $allColumns = array_merge($columns, $this->getExtraColumns());
            $response = [
                'draw' => (int)$draw,
                'recordsTotal' => (int)$originalQuery->count(),
                'recordsFiltered' => (int)$dataProvider->getTotalCount(),
                'data' => $this->formatData($filterQuery, $allColumns),
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }

        return $this->formatResponse($response);
    }

    /**
     * @param ActiveQuery $query
     * @param array $columns
     * @param array $search
     * @return ActiveQuery
     * @throws InvalidConfigException
     */
    public function applyFilter($query, $columns, $search)
    {
        if ($this->applyFilter !== null) {
            return call_user_func($this->applyFilter, $query, $columns, $search);
        }

        /** @var \yii\db\ActiveRecord $modelClass */
        // $modelClass = $query->modelClass;
        // $schema = $modelClass::getTableSchema()->columns;
        foreach ($columns as $column) {
            if ($column['searchable'] == 'true' && $column['data']) {
                $value = empty($search['value']) ? $column['search']['value'] : $search['value'];
                $query->orFilterWhere(['like', $column['data'], $value]);
            }
        }
        return $query;
    }

    /**
     * @param ActiveQuery $query
     * @param array $columns
     * @param array $order
     * @return ActiveQuery
     */
    public function applyOrder($query, $columns, $order)
    {
        if ($this->applyOrder !== null) {
            return call_user_func($this->applyOrder, $query, $columns, $order);
        }

        foreach ($order as $key => $item) {
            if (array_key_exists('orderable', $columns[$item['column']]) && $columns[$item['column']]['orderable'] === 'false') {
                continue;
            }
            $sort = $item['dir'] == 'desc' ? SORT_DESC : SORT_ASC;
            $query->addOrderBy([$columns[$item['column']]['data'] => $sort]);
        }
        return $query;
    }

    /**
     * @param ActiveQuery $query
     * @param array $columns
     * @return array|ActiveRecord[]
     */
    public function formatData($query, $columns)
    {
        return call_user_func($this->formatData, $query, $columns);
    }

    /**
     * @param array $response
     * @return array|ActiveRecord[]
     */
    public function formatResponse($response)
    {
        if ($this->formatResponse !== null) {
            return call_user_func($this->formatResponse, $response);
        }

        return $response;
    }

    /**
     * Prepare extraColumns for
     */
    protected function getExtraColumns()
    {
        return array_map(function ($column) {
            return ['data' => $column];
        }, $this->extraColumns);
    }

     /**
     * Extract param from request
     * @param $name
     * @param null $defaultValue
     * @return mixed
     */
    protected function getParam($name, $defaultValue = null)
    {
        return $this->requestMethod == self::REQUEST_METHOD_GET ?
            Yii::$app->request->getQueryParam($name, $defaultValue) :
            Yii::$app->request->getBodyParam($name, $defaultValue);
    }
}
