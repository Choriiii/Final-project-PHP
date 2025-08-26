<?php
interface Authorizable{//You shoulc always have these function!
    public function getRole();
    public function getID();
    public function can($permission, ?int $resourseOwnerID=null):bool;
}
abstract class User implements Authorizable{
    protected $id;
    protected $role;
    public function __construct($id,$role)
    {
        $this->id=$id;
        $this->role=$role;
    }
    
    public function getID(){return $this->id;}//こいつらいるの？？？？？
    public function getRole(){return $this->role;}//こいつらいるの？？？？？

    abstract public function can($permission, ? int $resourseOwnerID=null):bool;//こいつなに？
}
class Admin extends User{
    public function can($permission, ?int $resourseOwnerID=null):bool{
        return true;
    }
};
class Editor extends User{
    public function can($permission, ?int $resourseOwnerID=null):bool{
        if($permission==='view_post' && $resourseOwnerID === $this->id) return true;        
        return false;
    }
}
class Viewer extends User{
    public function can($permission, ?int $resourseOwnerID=null):bool{
        if($permission==='view_post')return true;
        return false; 
    }
}

//manage the DB connection
class DataBase{

}

class UserManager{
    private $db;
    public function __construct($db)
    {
        $this->db=$db;
    }
    public function user_edit(User $act, int $userID, $newData){
        if(!$act->can('userEdit',$userID)) {
            return false;
        }else{
            $sql = $this->db->prepare("UPDATE userdata SET UserName=?, EmailAddress=?, Role=? WHERE UserID=?");
            $sql->bind_param("sssi",$newData['UserName'],$newData['EmailAddress'],$newData['Role'],$userID);
            $sql->execute();
            $sql->close();

            return true;
        }
    }
    public function user_delete(User $act, int $userID){
        if(!$act->can('userEdit',$userID)){
            return false;
        }else{
            $sql = $this->db->prepare("DELETE FROM userdata WHERE UserID=?");
            $sql->bind_param("i",$userID);
            $sql->execute();
            $sql->close();

            return true;
        }
    }
}
//Class for managing post data
/*
親User
：全機能(adminが使えるすべての機能)(こいつを子ごとに入れたり入れなかったり調整)
　-商品の追加・編集・削除、ユーザーデータの編集、audit logの閲覧、
　 user権限の変更(これできるのかな...?)→user管理ページ作ればいけるわ

子
Admin:全権限
Editor:商品の操作
Viewer:全権限なし

多分class Userじゃなくて、editorにして、そこから拡張がいいかもしれない...継承使うなら(要確認)
*/
