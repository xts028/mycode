<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Home\Model;

/**
 * Description of ArticleCategoryModel
 *
 * @author Administrator
 */
class ArticleCategoryModel extends \Think\Model{
    public function getHelpArticleList() {
        $article_categories = $this->where(['is_help'=>1,'status'=>1])->order('sort')->getField('id,name');
        $article_model = M('Article');
        $article_list = [];
        foreach($article_categories as $cat_id=>$cat_name){
            $article_list[$cat_id] = $article_model->where(['article_category_id'=>$cat_id,'status'=>1])->order('sort')->limit(6)->getField('id,name');
        }
        return compact('article_categories','article_list');
    }
}
