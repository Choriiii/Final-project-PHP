<?php
interface Authorizable{//You should always have these function!
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

    abstract public function can($permission, ? int $resourseOwnerID=null):bool;//always return true or false depends witch permission u have
}
class Admin extends User{//u can do anything with this Admin User
    public function can($permission, ?int $resourseOwnerID=null):bool{
        return true;
    }
};
class Editor extends User{
    public function can($permission, ?int $resourseOwnerID=null):bool{
        if($permission==='userEdit' && $resourseOwnerID === $this->id) return true;//if it's your userData, u can edit it.        
        return false;
    }
}
class Viewer extends User{
    public function can($permission, ?int $resourseOwnerID=null):bool{
        if($permission==='view_post')return true;//you can only view the posts.
        return false; 
    }
}


class UserManager{
    private $db;
    public function __construct($db)
    {
        $this->db=$db;
    }
    public function user_edit(User $act, int $userID, $newData){//function for user data editing
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
    public function user_add(User $act, $newData){
        if(!$act->can('userAdd')){
            return false;
        }else{
            $sql = $this->db->prepare("INSERT INTO userdata(UserName, EmailAddress, Password, Role) VALUE (?,?,?,?)");
            $sql->bind_param("ssss",$newData['UserName'],$newData['EmailAddress'],$newData['Password'],$newData['Role']);
            $sql->execute();
            $sql->close();

            return true;
        }
    }
}
