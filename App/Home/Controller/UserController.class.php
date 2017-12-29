<?php

namespace Home\Controller;
use Home\Controller\CommonController;
use Think\GoodPage;

class UserController extends CommonController {

    //个人中心-我的订单
    public function index(){
        $u = session('user');
        if(session('pro_id')){
            $id = session('pro_id');
            $this->redirect('Index/info',array('id'=>$id));
        }

        $applyDB = M('Apply');
        $where['uid']=$u['id'];
        $count = $applyDB -> where($where) -> count();
        $Page = new GoodPage($count,15);
        $pageshow = $Page->show();
        $list = $applyDB->where($where) ->  limit($Page->firstRow.','.$Page->listRows)->order('ctime desc')->select();
        foreach($list as $ka=>$va){
            $p = M('Products')->where(['id'=>$va['pid']])->find();
            $list[$ka]['title']=$p['title'];
            $list[$ka]['price']=intval($p['price']);
            switch($va['status']){
                case 0:$list[$ka]['state']='<span style="color: #f60;">审核中</span>';break;
                case 1:$list[$ka]['state']='<span style="color: green;">申请成功</span>';break;
                case 2:$list[$ka]['state']='已取消';break;
            }
            $list[$ka]['store']=M('Stores')->where(['id'=>$va['sid']])->getField('title');
            $cover_img = M('ProImg')->where(['n_id'=>$va['pid'],'type'=>1,'flag'=>1])->getField('name');
            $list[$ka]['img']=$cover_img?$cover_img:M('ProImg')->where(['n_id'=>$va['pid'],'type'=>1])->order('f_id asc')->getField('name');
            $list[$ka]['ctime']=date('Y-m-d H:i:s',$va['ctime']);
        }
        $this->assign('list',$list);
        $this->assign('page',$pageshow );
        $this->display();
    }

    //订单详情
    public function order(){
        $applyDB = M('Apply');
        $commentDB = M('Comments');
        $info = $applyDB->where(['id'=>$_GET['id']])->find();
        $p = M('Products')->where(['id'=>$info['pid']])->find();
        $info['store']=M('Stores')->where(['id'=>$info['sid']])->getField('title');
        switch($info['status']){
            case 0:$info['state']='<span style="color: #f60;">审核中</span>';break;
            case 1:$info['state']='<span style="color: green;">申请成功</span>';break;
            case 2:$info['state']='已取消';break;
        }
        $aid = M('Stores')->where(['id'=>$info['sid']])->getField('aid');
        $info['city']=M('Area')->where(['id'=>$aid])->getField('name');
        $info['etime'] = date('Y-m-d',$info['etime']);
        $p['price']=intval($p['price']);
        $cover_img = M('ProImg')->where(['n_id'=>$p['id'],'type'=>1,'flag'=>1])->getField('name');
        $p['img']=$cover_img?$cover_img:M('ProImg')->where(['n_id'=>$p['id'],'type'=>1])->order('f_id asc')->getField('name');
        $this->assign('pro',$p);
        $this->assign('info',$info);

        //用户评价
        $ping = $commentDB->where(['oid'=>$_GET['id']])->find();
        if($ping) {
            $pu = M('User')->where(['id' => $ping['uid']])->getField('phone');
            $ping['phone'] = substr_replace($pu, '****', 3, 4);
        }
        $this->assign('ping',$ping);
        $this->display();
    }

    //用户评价
    public function comment(){
        $commentDB = M('Comments');
        $u = session('user');
        if (I('get.submit')){
            $data['uid']=$u['id'];
            $data['oid']=$_POST['id'];
            $data['star']=$_POST['star'];
            $data['content']=$_POST['con'];
            $data['ctime']=time();
            $is=$commentDB->add($data);
            if($is){
                $re = array('code'=>'000','msg'=>'评价成功');
            }else{
                $re = array('code'=>'001','msg'=>'评价失败');
            }
            echo json_encode($re);exit;
        }
    }

    //预约申请处理
    public function apply(){
        $u = session('user');
        $applyDB = M('Apply');
        $model = M();
        if (I('get.submit')){
            $data['uid']=$u['id'];
            $data['pid']=$_POST['id'];
            $data['name']=$_POST['name'];
            $data['sex']=$_POST['sex'];
            $data['phone']=$_POST['phone'];
            $data['etime']=strtotime($_POST['etime']);
            $data['sid']=$_POST['sid'];
            $data['ctime']=time();
            $code = $_POST['code'];
            $phone_code = session('phone_code');
            if($phone_code!=$code){
                $re = array('code'=>'001','msg'=>'验证码不正确');
                echo json_encode($re);exit;
            }
            $is = $applyDB->add($data);
            if($is){
                session('phone_code',null);
                $sql = "update `app_products` set hit=hit+1 where id=".$data['pid'];
                $model->execute($sql);
                $re = array('code'=>'000','msg'=>'申请成功');
            }else{
                $re = array('code'=>'002','msg'=>'申请失败');
            }
            echo json_encode($re);exit;
        }
    }

    //个人中心-个人资料
    public function my(){
        $u = session('user');
        if (I('get.submit')){
            $sex = I('post.sex', '', 'trim');
            $broth = I('post.broth', '', 'trim');
            $old = M('UserInfo')->where(['uid'=>$u['id']])->find();
            $data['sex']= $sex;
            $data['broth']= $broth;
            if($old) {
                M('UserInfo')->where(['id' => $old['id']])->save($data);
            }else{
                $data['uid']= $u['id'];
                $data['ctime']= time();
                M('UserInfo')->add($data);
            }
            $re = array('code'=>'000','msg'=>'编辑成功');
            echo json_encode($re);exit;
        }
        $us = M('UserInfo')->where(['uid'=>$u['id']])->find();
        $this->assign('us',$us);
        $this->display();
    }

    //个人中心-信用信息
    public function xin(){
        $u = session('user');
        if (I('get.submit')){
            $data['wid']=$_POST['wid'];
            $data['pid']=$_POST['pid'];
            $data['sid']=$_POST['sid'];
            $data['gid']=$_POST['gid'];
            $data['xid']=$_POST['xid'];
            $data['zid']=$_POST['zid'];
            $old = M('UserXin')->where(['uid'=>$u['id']])->find();
            if($old){
                $is = M('UserXin')->where(['id'=>$old['id']])->save($data);
            }else{
                $data['uid']=$u['id'];
                $data['ctime']=time();
                $is = M('UserXin')->add($data);
            }
            if($is){
                $re = array('code'=>'000','msg'=>'保存成功');
            }else{
                $re = array('code'=>'001','msg'=>'保存失败');
            }
            echo json_encode($re);exit;
        }
        $this->assign('work',C('WORK'));
        $this->assign('pay',C('PAY'));
        $this->assign('bao',C('BAO'));
        $this->assign('jin',C('JIN'));
        $this->assign('xin',C('XIN'));
        $this->assign('zhu',C('ZHU'));
        $me = M('UserXin')->where(['uid'=>$u['id']])->find();
        $me['did']=$me['sid'];
        $this->assign('me',$me);
        $this->display();
    }

    //个人中心-实名验证
    public function name(){
        $u = session('user');
        $my = M('UserInfo')->where(['uid'=>$u['id']])->find();
        if(!empty($my['card'])){
            $my['status']=1;
        }
        if(strlen($my['card'])==15){
            $my['card']=substr_replace($my['card'], '****', 8, 4);
        }
        if(strlen($my['card'])==18){
            $my['card']=substr_replace($my['card'], '****', 10, 4);
        }
        $this->assign('my',$my);
        $this->display();
    }

    //个人中心-修改密码
    public function setting(){
        $u = session('user');
        if (I('get.msg')){
            $code = I('post.code', '', 'trim');
            $phone_code = session('phone_code');
            if($phone_code!=$code){
                $re = array('code'=>'001','msg'=>'验证码不正确');
                echo json_encode($re);exit;
            }
            $re = array('code'=>'000','msg'=>'验证成功','num'=>$code);
            echo json_encode($re);exit;
        }
        if (I('get.submit')) {
            $password = I('post.password', '', 'trim');
            $repass = I('post.repass', '', 'trim');
            $old = M('User')->where(['id'=>$u['id']])->find();
            if($password!=$repass){
                $re = array('code'=>'002','msg'=>'您两次输入的密码不一致');
                echo json_encode($re);exit;
            }
            if($old['password']==md5($password)){
                $re = array('code'=>'003','msg'=>'新密码不能与旧密码一样');
                echo json_encode($re);exit;
            }
            $data['password']=md5($password);
            $where['id']=$u['id'];
            $is = M('User')->where($where)->save($data);
            if($is){
                session('user', null);
                session('phone_code', null);
                $re = array('code'=>'000','msg'=>'修改成功,请重新登录');
                echo json_encode($re);exit;
            }else{
                $re = array('code'=>'004','msg'=>'修改失败');
                echo json_encode($re);exit;
            }
        }
        if(I('get.code')&&I('get.code')==session('phone_code')){
            $this->assign('pass_code',1);
        }
        $this->display();
    }

    //用户登录
    public function login(){
        if(session('user')){
            $this->redirect('Index/index');
        }
        $userDB = M('User');
        if (I('get.msg')){
            $phone = I('post.phone', '', 'trim');
            $code = I('post.code', '', 'trim');
            $phone_code = session('phone_code');
            if($phone==''){
                $re = array('code'=>'001','msg'=>'手机号码不能为空');
                echo json_encode($re);exit;
            }
            if($phone_code!=$code){
                $re = array('code'=>'002','msg'=>'验证码不正确');
                echo json_encode($re);exit;
            }

            $info = $userDB->where(array('phone'=>$phone))->find();
            if(!$info){
                $re = array('code'=>'003','msg'=>'该手机暂未注册');
                echo json_encode($re);exit;
            }

            session('user', $info);
            $re = array('code'=>'000','msg'=>'登录成功');
            echo json_encode($re);exit;
        }

        if (I('get.submit')){
            $phone = I('post.phone', '', 'trim');
            $code = I('post.code', '', 'trim');
            $password = I('post.password', '', 'trim');
            if($phone==''){
                $re = array('code'=>'001','msg'=>'手机号码不能为空');
                echo json_encode($re);exit;
            }

            if($password==''){
                $re = array('code'=>'002','msg'=>'密码不能为空');
                echo json_encode($re);exit;
            }

            if(!check_verify($code, 'home')){
                $re = array('code'=>'003','msg'=>'验证码不正确');
                echo json_encode($re);exit;
            }

            $info = $userDB->where(array('phone'=>$phone))->find();
            if(!$info){
                $re = array('code'=>'004','msg'=>'该手机暂未注册');
                echo json_encode($re);exit;
            }

            if($info['password']!=md5($password)){
                $re = array('code'=>'005','msg'=>'密码不正确');
                echo json_encode($re);exit;
            }

            session('user', $info);
            $re = array('code'=>'000','msg'=>'登录成功');
            echo json_encode($re);exit;

        }

        $this->display();
    }

    //退出登录
    public function logout() {
        session('user', null);
        session('phone_code', null);

        $this->redirect('Index/index');
    }

    //用户注册
    public function register(){
        if(session('user')){
            $this->redirect('Index/index');
        }
        $userDB = M('User');
        if (I('get.submit')){
            $phone = I('post.phone', '', 'trim');
            $code = I('post.code', '', 'trim');
            $password = I('post.password', '', 'trim');
            $phone_code = session('phone_code');
            if($phone==''){
                $re = array('code'=>'001','msg'=>'手机号码不能为空');
                echo json_encode($re);exit;
            }
            if($phone_code!=$code){
                $re = array('code'=>'002','msg'=>'验证码不正确');
                echo json_encode($re);exit;
            }
            if($password==''){
                $re = array('code'=>'003','msg'=>'密码不能为空');
                echo json_encode($re);exit;
            }
            if($userDB->where(array('phone'=>$phone))->field('id')->find()){
                $re = array('code'=>'004','msg'=>'该手机号码已注册');
                echo json_encode($re);exit;
            }
            $data['phone'] = $phone;
            $data['username'] = $phone;
            $data['password'] = md5($password);
            $data['postdate'] = time();
            $id = $userDB->add($data);
            if($id){
                $data['id']=$id;
                session('user', $data);
                $re = array('code'=>'000','msg'=>'注册成功');
            }else {
                $re = array('code'=>'005','msg'=>'注册失败');
            }
            echo json_encode($re);exit;
        }
        $this->display();
    }

    //忘记密码
    public function pass(){
        $userDB = M('User');
        if (I('get.submit')) {
            $phone = I('post.phone', '', 'trim');
            $code = I('post.code', '', 'trim');
            if(!check_verify($code, 'home')){
                $re = array('code'=>'001','msg'=>'验证码不正确');
                echo json_encode($re);exit;
            }
            if(!$userDB->where(array('phone'=>$phone))->field('id')->find()){
                $re = array('code'=>'002','msg'=>'该手机号码暂未注册');
                echo json_encode($re);exit;
            }
            cookie('set_phone',$phone,300);
            $re = array('code'=>'000','msg'=>'验证成功');
            echo json_encode($re);exit;
        }
        $this->display();
    }

    //忘记密码-验证手机
    public function pass_1(){
        if (I('get.submit')) {
            $phone = I('post.phone', '', 'trim');
            $code = I('post.code', '', 'trim');
            $phone_code = session('phone_code');
            if($phone!=cookie('set_phone')){
                $re = array('code'=>'001','msg'=>'非法操作');
                echo json_encode($re);exit;
            }
            if($phone_code!=$code){
                $re = array('code'=>'002','msg'=>'验证码不正确');
                echo json_encode($re);exit;
            }
            $re = array('code'=>'000','msg'=>'验证成功');
            echo json_encode($re);exit;
        }
        if(!cookie('set_phone')){
            $this->redirect('User/pass');
        }
        $phone = cookie('set_phone');
        $showPohone = insertToStr(insertToStr($phone,3,'-'),8,'-');
        $this->assign('phone',$phone);
        $this->assign('show',$showPohone);
        $this->display();
    }

    //忘记密码-重置密码
    public function pass_2(){
        if (I('get.submit')) {
            $phone = I('post.phone', '', 'trim');
            $password = I('post.password', '', 'trim');
            $repass = I('post.repass', '', 'trim');
            $old = M('User')->where(['phone'=>$phone])->find();
            if($phone!=cookie('set_phone')){
                $re = array('code'=>'001','msg'=>'非法操作');
                echo json_encode($re);exit;
            }
            if($password!=$repass){
                $re = array('code'=>'002','msg'=>'您两次输入的密码不一致');
                echo json_encode($re);exit;
            }
            if($old['password']==md5($password)){
                $re = array('code'=>'003','msg'=>'新密码不能与旧密码一样');
                echo json_encode($re);exit;
            }
            $data['password']=md5($password);
            $where['phone']=$phone;
            $is = M('User')->where($where)->save($data);
            if($is){
                $re = array('code'=>'000','msg'=>'重置成功');
                echo json_encode($re);exit;
            }else{
                $re = array('code'=>'004','msg'=>'重置失败');
                echo json_encode($re);exit;
            }
        }

        if(!cookie('set_phone')){
            $this->redirect('User/pass');
        }
        $phone = cookie('set_phone');
        $this->assign('phone',$phone);
        $this->display();
    }

    //忘记密码-找回成功
    public function pass_3(){
        $this->display();
    }

    //身份证验证
    public function check_card(){
        $u= session('user');
        $key = "af603be1447f25ce2307291d75d58f3b";
        $url = "http://v.apistore.cn/api/a1";
        $no = $_POST['card'];
        $name = $_POST['name'];
        $info = array("key" => $key, "cardNo" => $no,"realName"=>$name);
        $is = $this->postSMS($url, $info);
        $back = json_decode($is,true);
        if($back['error_code']=='0'){
            $old = M('UserInfo')->where(['uid'=>$u['id']])->find();
            $data['sname']= $name;
            $data['card']= $no;
            if($old){
                M('UserInfo')->where(['id'=>$old['id']])->save($data);
            }else{
                $data['uid']= $u['id'];
                $data['ctime']= time();
                M('UserInfo')->add($data);
            }
            $re = array('code'=>'000','msg'=>'验证成功');
        }else{
            $re = array('code'=>'001','msg'=>'验证失败');
        }
        echo json_encode($re);
        exit;
    }

    private function postSMS($url,$parameter){
        $ch = curl_init() ;
        curl_setopt($ch,CURLOPT_URL,$url) ;
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);    //设置为字符串方式
        curl_setopt($ch, CURLOPT_HEADER, false);        //禁止头部信息
        curl_setopt($ch, CURLOPT_NOBODY,true);          //显示页面内容
        curl_setopt($ch, CURLOPT_POST, true);           //POST方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $parameter);    //POST数据
        $result = curl_exec($ch) ;
        curl_close($ch) ;
        return $result;
    }

}