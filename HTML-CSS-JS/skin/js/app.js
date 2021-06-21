$(function(){
    'use strict';

    $('.js-fruits').click(function(){   
        if($('input[name="name"]').val()==''){
        	alert("Укажите название");
        	return false;
        }
        if($('input[name="weight"]').val()==0){
        	alert("Укажите вес");
        	return false;
        }
        let data = $("#add_fruit").serialize();
        $.post('/',data,function(res){
            if(res.status=="ok"){
                $("#new_fruit").before(res.tpl);
                $('#add_fruit')[0].reset();
    		}else{
    			alert("Ошибка");
    		}
        },'json');
        return false;
    });
});