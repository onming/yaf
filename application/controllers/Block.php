<?php
/**
 * 区块链
 *
 * @author onming(170893265@qq.com) by 2018/9/28
 * @copyright (c) onming.cn. All rights reserved.
 */
namespace app\controllers;
use app\core\internal;

class BlockController extends Yaf_Controller_Abstract {

    private $index;
    private $timestamp;
    private $data;
    private $previous_hash;
    private $random_str;
    private $hash;

    public function init()
    {

    }

    /**
     * 默认动作
     */
    public function indexAction($name = "Stranger") {
        dd('aa123');
        //1. fetch query
        $get = $this->getRequest()->getQuery("get", "default value");

        //2. fetch model
        $model = new SampleModel();

        //3. assign
        $this->getView()->assign("content", $model->selectSample());
        $this->getView()->assign("name", $name);

        //4. render by Yaf, 如果这里返回FALSE, Yaf将不会调用自动视图引擎Render模板
        return TRUE;
    }


}