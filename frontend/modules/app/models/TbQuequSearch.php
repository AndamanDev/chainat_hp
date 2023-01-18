<?php

namespace frontend\modules\app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\modules\app\models\TbQuequ;

/**
 * TbQuequSearch represents the model behind the search form about `frontend\modules\app\models\TbQuequ`.
 */
class TbQuequSearch extends TbQuequ
{
  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['q_ids', 'q_arrive_time', 'q_appoint_time', 'pt_visit_type_id', 'appoint_id', 'servicegroupid', 'quickly', 'serviceid', 'created_from', 'q_status_id', 'counterserviceid', 'tslotid', 'countdrug', 'qfinace'], 'integer'],
      [['q_num', 'q_timestp', 'cid', 'q_vn', 'q_hn', 'q_qn', 'rx_q', 'pt_name', 'doctor_id', 'doctor_name', 'created_at', 'updated_at', 'pt_pic', 'pt_sound', 'maininscl_name', 'u_id', 'token', 'age', 'queue_date', 'queue_time'], 'safe'],
    ];
  }

  /**
   * @inheritdoc
   */
  public function scenarios()
  {
    // bypass scenarios() implementation in the parent class
    return Model::scenarios();
  }

  /**
   * Creates data provider instance with search query applied
   *
   * @param array $params
   *
   * @return ActiveDataProvider
   */
  public function search($params)
  {
    $query = TbQuequ::find()->orderBy([
      'serviceid' => SORT_ASC,
      'q_appoint_time' => SORT_ASC,
    ]);

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
    ]);

    $this->load($params);

    if (!$this->validate()) {
      // uncomment the following line if you do not want to return any records when validation fails
      // $query->where('0=1');
      return $dataProvider;
    }

    $query->andFilterWhere([
      'q_ids' => $this->q_ids,
      'q_timestp' => $this->q_timestp,
      'q_arrive_time' => $this->q_arrive_time,
      'q_appoint_time' => $this->q_appoint_time,
      'pt_visit_type_id' => $this->pt_visit_type_id,
      'appoint_id' => $this->appoint_id,
      'servicegroupid' => $this->servicegroupid,
      'quickly' => $this->quickly,
      'serviceid' => $this->serviceid,
      'created_from' => $this->created_from,
      'q_status_id' => $this->q_status_id,
      'counterserviceid' => $this->counterserviceid,
      'tslotid' => $this->tslotid,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
      'countdrug' => $this->countdrug,
      'qfinace' => $this->qfinace,
      'queue_date' => $this->queue_date,
      'queue_time' => $this->queue_time,
    ]);

    $query->andFilterWhere(['like', 'q_num', $this->q_num])
      ->andFilterWhere(['like', 'cid', $this->cid])
      ->andFilterWhere(['like', 'q_vn', $this->q_vn])
      ->andFilterWhere(['like', 'q_hn', $this->q_hn])
      ->andFilterWhere(['like', 'q_qn', $this->q_qn])
      ->andFilterWhere(['like', 'rx_q', $this->rx_q])
      ->andFilterWhere(['like', 'pt_name', $this->pt_name])
      ->andFilterWhere(['like', 'doctor_id', $this->doctor_id])
      ->andFilterWhere(['like', 'doctor_name', $this->doctor_name])
      ->andFilterWhere(['like', 'pt_pic', $this->pt_pic])
      ->andFilterWhere(['like', 'pt_sound', $this->pt_sound])
      ->andFilterWhere(['like', 'maininscl_name', $this->maininscl_name])
      ->andFilterWhere(['like', 'u_id', $this->u_id])
      ->andFilterWhere(['like', 'token', $this->token])
      ->andFilterWhere(['like', 'age', $this->age]);


    return $dataProvider;
  }
}
