<?php

include('../__global.php');


class adm_wrcArticle extends admin_ctrl
{
    private $dataDefine;
    private $model;
    private $modelContent;

    public function _construct()
    {
        
        $this->dataDefine = 'wrcArticle';
        $this->model = new ml_model_wrcArticle($this->dataDefine);
        $this->modelContent = new ml_model_wrcArticleContent();
        //
    }
    
    protected function output($data)
    {
        $data['_dataDefine'] = $this->dataDefine;
        parent::output($data);
    }

    
    protected function run()
    {
        $Ym = $this->input('ym');
        $page = $this->input('p','g',1);
        $pagesize = $this->input('pagesize' , 'g' , 10);
        $srcId = $this->input('srcId');
        if (!$srcId) {
            $this->_redirect('adm_wrcSource.php');
        }
        $this->model->std_listBySrcIdByPage($srcId ,$Ym, $page , $pagesize);
        $data['rows'] = $this->model->get_data();
        $aJobContentId = array();


        $oSrc = new ml_model_wrcSource();
        $oSrc->std_getRowById($srcId);
        $aSrc = $oSrc->get_data();
        $category = $aSrc['category'];

        $oJobContent = new ml_model_wrcJobContent();
        $oJobContent->get_by_category($category , 1 , 0);
        $data['aJobContent'] = Tool_array::format_2d_array($oJobContent->get_data() , 'name' , Tool_array::FORMAT_ID2VALUE);

        $this->model->std_getCountBySrcId($srcId , $Ym);
        $data['total'] = $this->model->get_data();
        $data['pagesize'] = $pagesize;
        $data['srcId'] = $srcId;

        $this->output($data);
    }
    protected function page_addForm()
    {
        $this->output();
    }
    protected function page_editForm()
    {
        $id = $this->input('id');
        $this->model->std_getRowById($id);
        $data['row'] = $this->model->get_data();

        $oSrc = new ml_model_wrcSource();
        $oSrc->std_getRowById($data['row']['source_id']);
        $aSource = $oSrc->get_data();

        $ojc = new ml_model_wrcJobContent();
        $ojc->get_by_category($aSource['category']);
        $data['aJobContent'] = Tool_array::format_2d_array($ojc->get_data() , 'name' , Tool_array::FORMAT_ID2VALUE);
        $this->output($data);
    }
    protected function page_articleShow()
    {
        $id = $this->input('id');
        $this->model->std_getRowById($id);
        $data['articleRow'] = $this->model->get_data();
        
        $this->modelContent->std_getRowById($id);

        $data['articleRow'] = array_merge($data['articleRow'] , $this->modelContent->get_data());
        
        $this->output($data);
    }
    
    protected function api_add()
    {
        $dataDefine = ml_factory::load_dataDefine($this->dataDefine);

        foreach ($dataDefine['field'] as $key => $value) {
            $data[$key] = $this->input($key);
        }
        $this->model->std_addRow($data);
        $this->back();
    }
    protected function api_edit()
    {
        $dataDefine = ml_factory::load_dataDefine($this->dataDefine);

        $id = $this->input('id');
        foreach ($dataDefine['field'] as $key => $value) {
            $data[$key] = $this->input($key);
        }
        $this->model->std_updateRow($id , $data);
        $this->back();
    }
    protected function api_delById()
    {
        $id = $this->input('id');
        $this->model->std_delById($id);
        $this->back();
    }
    protected function api_changeJobContentIdById()
    {
        $id = $this->input('id');
        $jobContentId = $this->input('jobContentId');
        $this->model->std_updateRow($id , array('jobContentId' => array($jobContentId)));
        $this->back();
    }
    protected function api_reSegment()
    {
        $id = $this->input('id');
        $this->model->std_getRowById($id);
        $articleInfo = $this->model->get_data();

        $aTag = ml_function_lib::segmentChinese($articleInfo['title']);

        $oBiz = new ml_biz_articleTag2jobContent();
        $aJobContentId = $oBiz->execute($aTag);
        $dataUpdate['tags'] = $oBiz->getMetaTag();
        if(!empty($aJobContentId))
        {
            $dataUpdate['jobContentId'] = $aJobContentId;
        }

        $this->model->std_updateRow($id , $dataUpdate);

        $oBizA2r = new ml_biz_articleid2redis();
        $oBizA2r->execute($id , $dataUpdate['tags'] , $dataUpdate['jobContentId']);
        $this->back();
    }
}

new adm_wrcArticle();
?>
