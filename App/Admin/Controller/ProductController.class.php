<?php
namespace Admin\Controller;
use Think\Image;

use Org\Net\UploadFile;

use Admin\Controller\CommonController;
use Think\Controller;
use Think\Model;

class ProductController extends CommonController {
	//商品管理
    public function index(){
    	$productM = M('Products');
    	$sortM = M('ProductSort');
    	$proImg = M('ProImg');

    	if(I('get.dosubmit')){
    		if($_POST['status'] != ''){
    			$where['status']=$_POST['status'];
    			$this->assign('status',$_POST['status']);
    		}

    		if($_POST['stime'] != ''){
    			$where['date_time']=array('egt',strtotime($_POST['stime']));
    			$this->assign('stime',$_POST['stime']);
    		}

    		if($_POST['etime'] != ''){
    			$where['date_time']=array('elt',strtotime($_POST['etime']));
    			$this->assign('etime',$_POST['etime']);
    		}

    		if($_POST['title'] != ''){
    			$where['title']=array('like',"%{$_POST['title']}%");
    			$this->assign('title',$_POST['title']);
    		}

			$sql = '';
			if($_POST['sid'] != ''){
				$i = 1;
				foreach($_POST['sid'] as $ks=>$vs){
					if($vs!='0'){
						if($i==1){
							$sql = 'select pid as pid1 from app_pro_s where sid='.$vs;
						}else{
							$sql = 'select pid as pid'.$i.' from app_pro_s as s'.($i-1).' inner join ('.$sql.') as t'.($i-1).' on s'.($i-1).'.pid=t'.($i-1).'.pid'.($i-1).' where s'.($i-1).'.sid='.$vs;
						}
						$i++;
					}
				}
			}

			$model = new Model();
			$arr = $model->query($sql);
			if($arr){
				$t = $i-1;
				foreach($arr as $kw=>$vw){
					$f_ids[]=$vw['pid'.$t];
				}
				$where['id']=array('in',$f_ids);
			}else{
				if($_POST['sid'][0] != '0'&&$_POST['sid'][1] != '0'&&$_POST['sid'][2] != '0'){
					$where['id']=0;
				}
			}
    	}

    	$list = $productM -> where($where) -> order('date_time desc') -> select();
    	foreach($list as $k => $v){
    		switch ($v['status']){
    			case 0:$list[$k]['t']='<span class="label label-default radius">待审核</span>';break;
    			case 1:$list[$k]['t']='<span class="label label-success radius">已发布</span>';break;
    			case 2:$list[$k]['t']='<span class="label label-danger radius">未通过</span>';break;
    			case -1:$list[$k]['t']='<span class="label label-defaunt radius">已下架</span>';break;
    		}
    		$list[$k]['img'] = $proImg -> where('type=1 and n_id='.$v[id]) ->  select();
			//获取分类
			$cls = M('ProS')->where(['pid'=>$v['id']])->select();
			$show = '';
			foreach($cls as $ks=>$vs){
				$cname = $sortM->where(['id'=>$vs['sid']])->find();
				$fname = $sortM->where(['id'=>$cname['pid']])->getField('name');
				$show.=$fname.'->'.$cname['name'].'<br/>';
			}
			$list[$k]['cname'] = $show;
    	}
    	$num = $productM -> where($where) -> count();
    	$this->assign('list',$list);
    	$this->assign('num',$num);

    	//获取分类
    	$clist = $sortM->where(['level'=>1])->order('sort asc')->select();
    	foreach ($clist as $k=>$v){
    		$zi = $sortM->where(['level'=>2,'pid'=>$v['id']])->order('sort asc')->select();
    		foreach ($zi as $kl=>$vl){
				if(in_array($vl['id'],$_POST['sid'])){
					$zi[$kl]['selected']='selected';
				}else{
					$zi[$kl]['selected']='';
				}
    			$zi_l = $sortM->where(['level'=>3,'pid'=>$vl['id']])->order('sort asc')->select();
    			$zi[$kl]['zi']=$zi_l;
    		}
    		$clist[$k]['zi']=$zi;
    	}
    	$this->assign('clist',$clist);

    	$this->display();
    }

    //商品添加
    public function add(){
		$product = D('Products');
		$proImg = D('ProImg');
		$proS = M('ProS');

    	if(I('get.dosubmit')){
    		$flag = 1;
    		//不开启表单令牌验证，但是用这个方法可以进行数据的自动获取和自动验证
    		if(!$product -> create()){
    			$re = array("error"=>0,"msg"=>$product -> getError());
    			echo json_encode($re);
    			exit();
    		}

    		$product -> date_time = strtotime($_POST['date_time']);
    		$product -> stime = $_POST['stime']?strtotime($_POST['stime']):NULL;
    		$product -> etime = $_POST['etime']?strtotime($_POST['etime']):NULL;
    		$product -> content = htmlspecialchars_decode(html_entity_decode($_POST['content']));
    		if(!$id = $product -> add()){
    			$flag = 0;
    		}

    		if($_POST['img_name']){
    			for($i=0;$i<count($_POST['img_name']);$i++){
    				if($_POST['img_name'][$i] != 'undefined'){
    					$data['type'] = 1;
    					$data['n_id'] = $id;
    					$data['size'] = $_POST['img_size'][$i];
    					$data['title'] = $_POST['img_title'][$i];
    					$data['name'] = $_POST['img_name'][$i];
    					if(!$proImg -> add($data)){
    						$flag = 0;
    					}
    				}
    			}
    		}

			if($_POST['sid']){
				foreach($_POST['sid'] as $ks=>$vs){
					$dataLi[]=array('pid'=>$id,'sid'=>$vs);
				}
				$proS->addAll($dataLi);
			}

    		if($flag == 1){
    			$re = array("error"=>0,"msg"=>"保存成功");
    		}else{
    			$re = array("error"=>1,"msg"=>"保存失败");
    		}
    		echo json_encode($re);
    		exit;
    	}

    	$sortM = M('ProductSort');
    	$CK =  editor('content','','Editor','99%','400');
    	$this -> assign('fck',$CK);
    	$clist = $sortM->where(['level'=>1])->order('sort asc')->select();
    	foreach ($clist as $k=>$v){
    		$zi = $sortM->where(['level'=>2,'pid'=>$v['id']])->order('sort asc')->select();
    		foreach ($zi as $kl=>$vl){
    			$zi_l = $sortM->where(['level'=>3,'pid'=>$vl['id']])->order('sort asc')->select();
    			$zi[$kl]['zi']=$zi_l;
    		}
    		$clist[$k]['zi']=$zi;
    	}
    	$this->assign('clist',$clist);
    	$this->assign('now',date('Y-m-d H:i:s'));

    	//获取类别
    	$classes = M('ProductClass')->select();
    	$this->assign('classes',$classes);
    	$this->display();
    }

    //商品编辑
    public function edit(){
		$product = D('Products');
		$proImg = D('ProImg');
		$proS = M('ProS');

    	if(I('get.dosubmit')){
    		if($product -> create()){
    			$id = $_POST['id'];
    			$proImg -> where('n_id='.$id) -> delete(); //先删掉再重新添加，这样做逻辑简单
	    		$flag = 1;
	    		//不开启表单令牌验证，但是用这个方法可以进行数据的自动获取和自动验证
	    		if(!$product -> create()){
	    			$re = array("error"=>0,"msg"=>$product -> getError());
	    			echo json_encode($re);
	    			exit();
	    		}

				if(!isset($_POST['ishui'])){
					$product -> ishui = 0;
				}
	    		$product -> date_time = strtotime($_POST['date_time']);
	    		$product -> stime = $_POST['stime']?strtotime($_POST['stime']):NULL;
	    		$product -> etime = $_POST['etime']?strtotime($_POST['etime']):NULL;
	    		$product -> content = htmlspecialchars_decode(html_entity_decode($_POST['content']));
	    		if(!$ids = $product -> save()){
	    			$flag = 0;
	    		}

	    		if($_POST['img_name']){
	    			$flag = 1;
	    			for($i=0;$i<count($_POST['img_name']);$i++){
	    				if($_POST['img_name'][$i] != 'undefined'){
	    					$data['type'] = 1;
	    					$data['n_id'] = $id;
	    					$data['title'] = $_POST['img_title'][$i];
	    					$data['name'] = $_POST['img_name'][$i];
	    					if(!$proImg -> add($data)){
	    						$flag = 0;
	    					}
	    				}
	    			}
	    		}

				$proS->where(['pid'=>$id])->delete();
				if($_POST['sid']){
					foreach($_POST['sid'] as $ks=>$vs){
						$dataLi[]=array('pid'=>$id,'sid'=>$vs);
					}
					$proS->addAll($dataLi);
				}

	    		if($flag == 1){
	    			$re = array("error"=>0,"msg"=>"保存成功");
	    		}else{
	    			$re = array("error"=>1,"msg"=>"保存失败");
	    		}
    		}else{
				$re = array("error"=>1,"msg"=>$product -> getError());
			}
			echo json_encode($re);
			exit;
    	}
    	$productM = M('Products');
    	$proImgM = M('ProImg');
    	$con = $productM -> where('id='.$_GET['id']) -> find();
    	$con['stime']=$con['stime']?date('Y-m-d H:i:s',$con['stime']):'';
    	$con['etime']=$con['etime']?date('Y-m-d H:i:s',$con['etime']):'';
    	$imgs = $proImgM -> where('type=1 and n_id='.$_GET['id'])->order('f_id asc') -> select();
    	$CK =  editor('content',$con['content'],'Editor','99%','400');
    	$this -> assign('fck',$CK);
    	$this -> assign('con',$con);
    	$this -> assign('imgs',$imgs);
    	$sortM = M('ProductSort');
    	$clist = $sortM->where(['level'=>1])->order('sort asc')->select();
    	foreach ($clist as $k=>$v){
    		$zi = $sortM->where(['level'=>2,'pid'=>$v['id']])->order('sort asc')->select();
    		foreach ($zi as $kl=>$vl){
				$isHave = $proS->where(['pid'=>$_GET['id'],'sid'=>$vl['id']])->find();
				if($isHave){
					$zi[$kl]['selected']='selected';
				}else{
					$zi[$kl]['selected']='';
				}
    			$zi_l = $sortM->where(['level'=>3,'pid'=>$vl['id']])->order('sort asc')->select();
    			$zi[$kl]['zi']=$zi_l;
    		}
    		$clist[$k]['zi']=$zi;
    	}
    	$this->assign('clist',$clist);

    	//获取类别
    	$classes = M('ProductClass')->select();
    	$this->assign('classes',$classes);
    	$this->display();
    }

	//图片管理
	public function pics(){
		$imgDB	 =	 M("ProImg");
		$where['n_id']=$_GET['id'];
		$count = $imgDB->where($where)->count();
		$list = $imgDB->where($where)->select();
		foreach ($list as $k=>$v){
			switch($v['type']){
				case 1:$list[$k]['t_name']='PC端';break;
				case 2:$list[$k]['t_name']='移动端';break;
			}
			if($v['flag']=='1'){
				$list[$k]['state']='封面';
			}else{
				$list[$k]['state']='图片';
			}
			$list[$k]['p_name']= M("Products")->where(['id'=>$v['n_id']])->getField('title');
		}
		$this->assign('count',$count);
		$this->assign('list',$list);
		$this->assign('id',$_GET['id']);
		$this->display();
	}

	//设置图片
	public function set_pic(){
		$imgDB	 =	 M("ProImg");
		$where['f_id']=$_POST['id'];
		$info = $imgDB->where($where)->find();
		if(!$info){
			$re = array("error"=>1,"msg"=>"非法操作");
			echo json_encode($re);
			exit;
		}
		if($info['flag']==$_POST['flag']){
			$re = array("error"=>1,"msg"=>"非法操作");
			echo json_encode($re);
			exit;
		}
		if($_POST['flag']=='1'){
			$imgDB->where(['flag'=>1,'n_id'=>$_POST['pid'],'type'=>$_POST['type']])->save(['flag'=>0]);
		}
		$data['flag']=$_POST['flag'];
		$is = $imgDB->where($where)->save($data);
		if($is){
			$re = array("error"=>0,"msg"=>"设置成功");
		}else{
			$re = array("error"=>1,"msg"=>"设置失败");
		}
		echo json_encode($re);
		exit;
	}

	//添加图片
	public function add_pic(){
		$imgDB	 =	 M("ProImg");
		if(I('get.dosubmit')){
			$data['type']=$_POST['type'];
			$data['name']=$_POST['img_name'];
			$data['title']=$_POST['img_title'];
			$data['n_id']=$_POST['n_id'];
			$data['size']=$_POST['img_size'];
			if(!isset($_POST['img_name'])||empty($_POST['img_name'])){
				$re = array("error"=>1,"msg"=>"请先上传图片");
				echo json_encode($re);
				exit();
			}
			$is = $imgDB->add($data);
			if($is){
				$re = array("error"=>0,"msg"=>"保存成功");
			}else{
				$re = array("error"=>1,"msg"=>"保存失败");
			}
			echo json_encode($re);
			exit;
		}
		$this->assign('n_id',$_GET['id']);
		$this->display();
	}

	//删除图片
	public function del_pic(){
		$imgDB	 =	 M("ProImg");
		$is = $imgDB->where(['f_id'=>$_POST['id']])->delete();
		if($is){
			$re = array("error"=>0,"msg"=>"删除成功");
		}else{
			$re = array("error"=>1,"msg"=>"删除失败");
		}
		echo json_encode($re);
		exit;
	}

    //商品删除
    public function delete(){
    	$productM = M('Products');
    	$where['id']=$_POST['id'];
    	if($productM->where($where)->delete()){
    		$re = array("error"=>0,"msg"=>"删除成功");
    	}else{
    		$re = array("error"=>1,"msg"=>"删除失败");
    	}
    	echo json_encode($re);
    	exit;
    }

    //商品审核
    public function audit(){
    	$productM = M('Products');
    	$where['id']=$_POST['id'];
    	$data['status']=$_POST['status'];
    	$data['audit_time']=time();
    	$productM->where($where)->save($data);
    	echo "yes";
    }

    //商品推荐
    public function istui(){
    	$productM = M('Products');
    	if(empty($_POST['ids'])){
    		$re = array('error'=>1,'msg'=>'请选择商品');
    		echo json_encode($re);
    		exit;
    	}
    	$where['id']=array('in',$_POST['ids']);
    	$data['istui']=1;
    	$num = $productM->where($where)->save($data);
    	if($num>0){
    		$re = array("error"=>0,"msg"=>"操作成功");
    	}else{
    		$re = array("error"=>1,"msg"=>"操作失败");
    	}
    	echo json_encode($re);
    	exit;
    }

    //商品取消推荐
    public function notui(){
    	$productM = M('Products');
    	if(empty($_POST['ids'])){
    		$re = array('error'=>1,'msg'=>'请选择商品');
    		echo json_encode($re);
    		exit;
    	}
    	$where['id']=array('in',$_POST['ids']);
    	$data['istui']=0;
    	$num = $productM->where($where)->save($data);
    	if($num>0){
    		$re = array("error"=>0,"msg"=>"操作成功");
    	}else{
    		$re = array("error"=>1,"msg"=>"操作失败");
    	}
    	echo json_encode($re);
    	exit;
    }

    //商品属性
    public function signs(){
    	$product_configdb = M('ProductConfig');
    	$list = $product_configdb->select();
    	foreach ($list as $k=>$v){
    		$pname = M('Products')->where(['id'=>$v['product_id']])->getField('title');
    		$list[$k]['pname']=$pname;
    	}
    	$num = $product_configdb->count();
    	$this->assign('num',$num);
    	$this->assign('list',$list);
    	$this->assign('id',$_GET['id']);
    	$this->display();
    }

    //添加商品属性
    public function addsign(){
    	$product_configdb = M('ProductConfig');
    	if(I('get.dosubmit')){
    		$data['name']=$_POST['name'];
    		$data['product_id']=$_POST['product_id'];
    		$data['price']=$_POST['price'];
    		$data['number']=$_POST['number'];
    		$data['amount']=$_POST['amount']?$_POST['amount']:'1000';
    		$data['updated_time']=time();
    		$data['status']=$_POST['status'];
    		$is = $product_configdb->add($data);
    		if($is){
    			$re = array("error"=>0,"msg"=>"保存成功");
    		}else{
    			$re = array("error"=>1,"msg"=>"保存失败");
    		}
    		echo json_encode($re);
    		exit;
    	}
    	$this->assign('pro_id',$_GET['id']);
    	$this->display();
    }

    //编辑商品属性
    public function editsign(){
    	$product_configdb = M('ProductConfig');
    	if(I('get.dosubmit')){
    		$data['name']=$_POST['name'];
    		$data['price']=$_POST['price'];
    		$data['number']=$_POST['number'];
    		$data['amount']=$_POST['amount']?$_POST['amount']:'1000';
    		$data['updated_time']=time();
    		$data['status']=$_POST['status'];
    		$is = $product_configdb->where(['id'=>$_POST['id']])->save($data);
    		if($is){
    			$re = array("error"=>0,"msg"=>"保存成功");
    		}else{
    			$re = array("error"=>1,"msg"=>"保存失败");
    		}
    		echo json_encode($re);
    		exit;
    	}
    	$info = $product_configdb->where(['id'=>$_GET['id']])->find();
    	$this->assign('info',$info);
    	$this->display();
    }

    //删除商品属性
    public function delsign(){
    	$product_configdb = M('ProductConfig');
    	$is = $product_configdb->where(['id'=>$_POST['id']])->delete();
    	if($is){
    		$re = array("error"=>0,"msg"=>"删除成功");
    	}else{
    		$re = array("error"=>1,"msg"=>"删除失败");
    	}
    	echo json_encode($re);
    	exit;
    }

    //分类管理
    public function sort(){
    	$sortM = M('ProductSort');
    	$list = $sortM->where(['level'=>1])->order('sort asc')->select();
    	$str.='{ id:0, pId:-1, name:"添加分类", open:true},';
    	foreach ($list as $k1=>$v1){
    		$str.='{ id:'.$v1['id'].', pId:0, name:"'.$v1['name'].'(一级)"},';
    		$second = $sortM->where(['level'=>2,'pid'=>$v1['id']])->order('sort asc')->select();
    		foreach ($second as $k2=>$v2){
    			$str.='{ id:'.$v2['id'].', pId:'.$v1['id'].', name:"'.$v2['name'].'(二级)"},';
    			$three = $sortM->where(['level'=>3,'pid'=>$v2['id']])->order('sort asc')->select();
    			foreach ($three as $k3=>$v3){
    				$str.='{ id:'.$v3['id'].', pId:'.$v2['id'].', name:"'.$v3['name'].'(三级)"},';
    			}
    		}
    	}
    	$this->assign('trees',$str);
    	$this->display();
    }

    //分类添加
    public function addsort(){
    	$sortM = M('ProductSort');
    	if(I('get.dosubmit')){
    		if($_POST['level']>'1'&&empty($_POST['one'])){
    			$re = array("error"=>1,"msg"=>"请选择一级分类");
    			echo json_encode($re);
    			exit;
    		}
    		if($_POST['level']=='3'&&empty($_POST['two'])){
    			$re = array("error"=>1,"msg"=>"请选择二级分类");
    			echo json_encode($re);
    			exit;
    		}

			$lev1 = $sortM->where(['level'=>1])->count();
			if($_POST['level']==1&&$lev1>2){
				$re = array("error"=>1,"msg"=>"只能添加3个一级分类");
				echo json_encode($re);
				exit;
			}

    		$data['name']=$_POST['name'];
    		$data['level']=$_POST['level'];
    		switch ($_POST['level']){
    			case 1:$data['pid']=0;break;
    			case 2:$data['pid']=$_POST['one'];break;
    			case 3:$data['tip']=$_POST['one'];$data['pid']=$_POST['two'];break;
    		}
    		$data['sort']=$_POST['sort']?$_POST['sort']:0;
    		$data['remark']=$_POST['remark'];
    		$is = $sortM->add($data);
    		if($is){
				$re = array("error"=>0,"msg"=>"保存成功");
			}else{
				$re = array("error"=>1,"msg"=>"保存失败");
			}
			echo json_encode($re);
			exit;
    	}
    	$this->display();
    }

    //分类编辑
    public function editsort(){
    	$sortM = M('ProductSort');

    	if(I('get.dosubmit')){
    		if($_POST['level']>'1'&&empty($_POST['one'])){
    			$re = array("error"=>1,"msg"=>"请选择一级分类");
    			echo json_encode($re);
    			exit;
    		}
    		if($_POST['level']=='3'&&empty($_POST['two'])){
    			$re = array("error"=>1,"msg"=>"请选择二级分类");
    			echo json_encode($re);
    			exit;
    		}
    		$data['name']=$_POST['name'];
    		//$data['level']=$_POST['level'];
    		switch ($_POST['level']){
    			case 1:$data['pid']=0;break;
    			case 2:$data['pid']=$_POST['one'];break;
    			case 3:$data['tip']=$_POST['one'];$data['pid']=$_POST['two'];break;
    		}
    		$data['sort']=$_POST['sort']?$_POST['sort']:0;
    		$data['remark']=$_POST['remark'];
    		$where['id']=$_POST['id'];
    		$is = $sortM->where($where)->save($data);
    		if($is){
    			$re = array("error"=>0,"msg"=>"保存成功");
    		}else{
    			$re = array("error"=>1,"msg"=>"保存失败");
    		}
    		echo json_encode($re);
    		exit;
    	}

    	$where['id']=$_GET['id'];
    	$info = $sortM->where($where)->find();
    	switch ($info['level']){
    		case 1:
    			$two = '';
    			$three = '';
    			break;
    		case 2:
    			$second = $sortM->where(['level'=>1])->select();
    			$two.='<div class="row cl">
						<label class="col-2 lh-30"> 一级分类：</label>
						<div class="formControls col-5">
							<span class="select-box" style="width:150px;">
							<select class="select" onchange="seltwo(this)" name="one">
    							<option value="" class="sel-level1"> 选择一级分类  </option>';
    							foreach ($second as $val){
    								if($info['pid']==$val['id']){
    									$two.='<option value="'.$val['id'].'" selected="selected" class="sel-level2"> '.$val['name'].'  </option>';
    								}else{
										$two.='<option value="'.$val['id'].'" class="sel-level2"> '.$val['name'].'  </option>';
    								}
    							}
							$two.='</select>
							</span>
						</div>
					</div>';
    			$three = '';
    			break;
    		case 3:
    			$second = $sortM->where(['level'=>1])->select();
    			$pid=$sortM->where(['id'=>$info['pid']])->getField('pid');
    			$two.='<div class="row cl">
						<label class="col-2 lh-30"> 一级分类：</label>
						<div class="formControls col-5">
							<span class="select-box" style="width:150px;">
							<select class="select" onchange="seltwo(this)" name="one" disabled="disabled">
    							<option value="" class="sel-level1"> 选择一级分类  </option>';
				    			foreach ($second as $val){
				    				if($pid==$val['id']){
				    					$two.='<option value="'.$val['id'].'" selected="selected" class="sel-level2"> '.$val['name'].'  </option>';
				    				}else{
				    					$two.='<option value="'.$val['id'].'" class="sel-level2"> '.$val['name'].'  </option>';
				    				}
				    			}
				    			$two.='</select>
							</span>
						</div>
					</div>';
    			$arr = $sortM->where(['level'=>2,'pid'=>$pid])->select();
    			$three.='<div class="row cl">
						<label class="col-2 lh-30"> 二级分类：</label>
						<div class="formControls col-5">
							<span class="select-box" style="width:150px;">
							<select class="select" name="two">
    							<option value="" class="sel-level1"> 选择二级分类  </option>';
				    			foreach ($arr as $val){
				    				if($info['pid']==$val['id']){
				    					$three.='<option value="'.$val['id'].'" selected="selected" class="sel-level2"> '.$val['name'].'  </option>';
				    				}else{
				    					$three.='<option value="'.$val['id'].'" class="sel-level2"> '.$val['name'].'  </option>';
				    				}
				    			}
				    			$three.='</select>
							</span>
						</div>
					</div>';
    			break;
    	}
    	$this->assign('two',$two);
    	$this->assign('three',$three);
    	$this->assign('info',$info);
    	$this->display();
    }

    //分类删除
    public function deletesort(){
    	$sortM = M('ProductSort');
    	$where['id']=$_POST['id'];
    	$info = $sortM->where($where)->find();
    	switch ($info['level']){
    		case 1:
    			$find['tip']=$_POST['id'];
    			$find['pid']=$_POST['id'];
    			$find['_logic'] = 'OR';
    			$have = $sortM->where($find)->find();
    			if($have){
    				$o = $sortM->where($find)->delete();
    				if($o){
    					$is=$sortM->where(['id'=>$_POST['id']])->delete();
    					if($is){
    						$re = array("error"=>0,"msg"=>"删除成功");
    					}else{
    						$re = array("error"=>1,"msg"=>"删除失败");
    					}
    				}else{
    					$re = array("error"=>1,"msg"=>"删除失败");

    				}
    			}else{
    				$is=$sortM->where(['id'=>$_POST['id']])->delete();
    				if($is){
    					$re = array("error"=>0,"msg"=>"删除成功");
    				}else{
    					$re = array("error"=>1,"msg"=>"删除失败");
    				}
    			}
    			break;
    		case 2:
    			$have = $sortM->where(['pid'=>$_POST['id']])->find();
    			if($have){
	    			$o = $sortM->where(['pid'=>$_POST['id']])->delete();
	    			if($o){
	    				$is=$sortM->where(['id'=>$_POST['id']])->delete();
	    				if($is){
	    					$re = array("error"=>0,"msg"=>"删除成功");
	    				}else{
	    					$re = array("error"=>1,"msg"=>"删除失败");
	    				}
	    			}else{
	    				$re = array("error"=>1,"msg"=>"删除失败");
	    			}
    			}else{
    				$is=$sortM->where(['id'=>$_POST['id']])->delete();
    				if($is){
    					$re = array("error"=>0,"msg"=>"删除成功");
    				}else{
    					$re = array("error"=>1,"msg"=>"删除失败");
    				}
    			}
    			break;
    		case 3:
    			$is=$sortM->where(['id'=>$_POST['id']])->delete();
    			if($is){
    				$re = array("error"=>0,"msg"=>"删除成功");
    			}else{
    				$re = array("error"=>1,"msg"=>"删除失败");
    			}
    			break;
    	}
    	echo json_encode($re);
    	exit;
    }

    //ajax查找分类
    public function findsort(){
    	$sortM = M('ProductSort');
    	$where['level']=$_POST['level']-1;
    	if($_POST['pid']){
    		$where['pid']=$_POST['pid'];
    	}
    	$list = $sortM->where($where)->select();
    	switch ($_POST['level']){
    		case 2:$vn = '一';$ve = 'one';$cl='onchange="seltwo(this)"';break;
    		case 3:$vn = '二';$ve = 'two';$cl='';break;
    	}
    	if($list){
    		$str.='<div class="row cl">
						<label class="col-2 lh-30">'.$vn.'级分类：</label>
						<div class="formControls col-5">
							<span class="select-box" style="width:150px;">
							<select class="select" '.$cl.' name="'.$ve.'">
    							<option value="" class="sel-level1"> 选择'.$vn.'级分类  </option>';
    							foreach ($list as $val){
									$str.='<option value="'.$val['id'].'" class="sel-level2"> '.$val['name'].'  </option>';
    							}
							$str.='</select>
							</span>
						</div>
					</div>';
			echo $str;
    	}else{
    		echo 0;
    	}
    }

    //类别管理
    public function classes(){
    	$product_classdb = M('ProductClass');
    	$list = $product_classdb->select();
    	foreach ($list as $k=>$v){
    		if($v['state']=='1'){
    			$list[$k]['state']='显示';
    		}else{
    			$list[$k]['state']='不显示';
    		}
    	}
    	$num = $product_classdb -> count();
    	$this->assign('list',$list);
    	$this->assign('num',$num);
    	$this->display();
    }

    //类别添加
    public function addclass(){
    	$product_classdb = M('ProductClass');
    	if(I('get.dosubmit')){
    		$data['name']=$_POST['name'];
    		$data['banner_pic']=$_POST['img_name'];
    		$data['sort']=$_POST['sort'];
    		$data['link']=$_POST['link'];
    		$data['state']=$_POST['state'];
    		if(!isset($_POST['img_name'])||empty($_POST['img_name'])){
    			$re = array("error"=>1,"msg"=>"请先上传图片");
    			echo json_encode($re);
    			exit();
    		}
    		$is = $product_classdb->add($data);
    		if($is){
    			$re = array("error"=>0,"msg"=>"保存成功");
    		}else{
    			$re = array("error"=>1,"msg"=>"保存失败");
    		}
    		echo json_encode($re);
    		exit;
    	}
    	$this->display();
    }

    //类别编辑
    public function editclass(){
    	$product_classdb = M('ProductClass');
    	if(I('get.dosubmit')){
    		$data['name']=$_POST['name'];
    		$data['banner_pic']=$_POST['img_name'];
    		$data['sort']=$_POST['sort'];
    		$data['link']=$_POST['link'];
    		$data['state']=$_POST['state'];
    		if(!isset($_POST['img_name'])||empty($_POST['img_name'])){
    			$re = array("error"=>1,"msg"=>"请先上传图片");
    			echo json_encode($re);
    			exit();
    		}
    		$is = $product_classdb->where(['id'=>$_POST['id']])->save($data);
    		if($is){
    			$re = array("error"=>0,"msg"=>"保存成功");
    		}else{
    			$re = array("error"=>1,"msg"=>"保存失败");
    		}
    		echo json_encode($re);
    		exit;
    	}
    	$info = $product_classdb->where(['id'=>$_GET['id']])->find();
    	$this->assign('info',$info);
    	$this->display();
    }

    //类别删除
    public function delclass(){
    	$product_classdb = M('ProductClass');
    	$is = $product_classdb->where(['id'=>$_POST['id']])->delete();
    	if($is){
    		$re = array("error"=>0,"msg"=>"删除成功");
    	}else{
    		$re = array("error"=>1,"msg"=>"删除失败");
    	}
    	echo json_encode($re);
    	exit;
    }

	//门店管理
	public function stores(){
		$storesM = M('Stores');
		$list = $storesM->select();
		foreach($list as $k=>$v){
			$list[$k]['aid']=M('Area')->where(['id'=>$v['aid']])->getField('name');
			switch($v['status']){
				case 0:$list[$k]['status']='关闭';break;
				case 1:$list[$k]['status']='开启';break;
			}
		}
		$this->assign('list',$list);
		$this->display();
	}

	//添加门店
	public function addstore(){
		$areaM = M('Area');
		$storesM = M('Stores');
		if(I('get.dosubmit')){
			$data['aid']=$_POST['aid'];
			$data['title']=$_POST['title'];
			$data['status']=$_POST['status'];
			$data['ctime']=time();
			$is = $storesM->add($data);
			if($is){
				$re = array("error"=>0,"msg"=>"保存成功");
			}else{
				$re = array("error"=>1,"msg"=>"保存失败");
			}
			echo json_encode($re);
			exit;

		}
		$area = $areaM->where(['parent_id'=>6,'level'=>2])->select();
		$this->assign('area',$area);
		$this->display();
	}

	//编辑门店
	public function editstore(){
		$areaM = M('Area');
		$storesM = M('Stores');
		if(I('get.dosubmit')){
			$data['aid']=$_POST['aid'];
			$data['title']=$_POST['title'];
			$data['status']=$_POST['status'];
			$is = $storesM->where(['id'=>$_POST['id']])->save($data);
			if($is){
				$re = array("error"=>0,"msg"=>"保存成功");
			}else{
				$re = array("error"=>1,"msg"=>"保存失败");
			}
			echo json_encode($re);
			exit;
		}
		$info = $storesM->where(['id'=>$_GET['id']])->find();
		$area = $areaM->where(['parent_id'=>6,'level'=>2])->select();
		$this->assign('area',$area);
		$this->assign('info',$info);
		$this->display();
	}

	//删除门店
	public function delstore(){
		$storesM = M('Stores');
		$is = $storesM->where(['id'=>$_POST['id']])->delete();
		if($is){
			$re = array("error"=>0,"msg"=>"删除成功");
		}else{
			$re = array("error"=>1,"msg"=>"删除失败");
		}
		echo json_encode($re);
		exit;
	}

    //异步图片上传
    public function ajaxImgUp(){
    	$proImg = M('ProImg');
    	//过滤检测
    	$info = $this -> up();
    	if($info[0][flag] != false){
    		$info[0]['mes'] = '上传成功！';
    		echo json_encode($info[0]);
    	}else{
    		$info[0]['mes'] = $info[0]['error'];
    		echo json_encode($info[0]);
    	}
    }

    //过滤检测函数
    private function up(){
    	$upload=new UploadFile();
    	$upload->maxSize= '10485760';  //10M //是指上传文件的大小，默认为-1,不限制上传文件大小bytes
    	$upload->savePath='./Public/upload/product/';       //上传保存到什么地方？路径建议大家已主文件平级目录或者平级目录的子目录来保存
    	$upload->saveRule=time;    //上传文件的文件名保存规则  time uniqid  com_create_guid  uniqid
    	$upload->saveExt = 'png';
    	$upload->uploadReplace=true;     //如果存在同名文件是否进行覆盖
    	$upload->allowExts=array('jpg','jpeg','png','gif');     //准许上传的文件后缀
    	$upload->allowTypes=array('image/png','image/jpg','image/pjpeg','image/gif','image/jpeg');  //检测mime类型

    	//upload()  如果上传成功，返回ture,失败返回false

    	if($upload->upload()){
    		//局部变量，你可以在此处，保存到一个超全局变量当中去进行返回
    		$info=$upload->getUploadFileInfo();
    		$info[0][flag] = true;

    		//生成等比例的缩略图
    		$oldName='./Public/upload/product/'.$info[0][savename];
    		$img = new Image();
    		$img->open($oldName);
    		$width = $img->width(); // 返回图片的宽度
    		$height = $img->height(); // 返回图片的高度
    		$img_type = $img->type();
    		$thumb_min_name= UPLOAD_PATH .'product/s_'.$info[0][savename];
    		$s_w=$width*0.3;
    		$s_h=$height*0.3;
    		$img->thumb($s_w, $s_h)->save($thumb_min_name);
    		return $info;
    	}else{
    		//是专门来获取上传的错误信息的
    		$info[0][flag] = false;
    		$info[0][error] = $upload->getErrorMsg();
    		return $info;
    	}
    }
}