<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
defined('iPHP') OR exit('What are you doing?');
admincp::head();
?>
<script type="text/javascript">
window.catchRemoteImageEnable = <?php echo iCMS::$config['article']['catch_remote']?'true':'false';?>;
</script>
<script type="text/javascript" charset="utf-8" src="./app/admincp/ui/iCMS.ueditor.js"></script>
<script type="text/javascript" charset="utf-8" src="./app/admincp/ui/ueditor/ueditor.all.min.js"></script>
<script type="text/javascript">
$(function(){
  $("#title").focus();

  iCMS.editor.create('editor-body-1');
	$(".editor-page").change(function(){
		$(".editor-container").hide();
		$("#editor-wrap-"+this.value).show();
    iCMS.editor.create('editor-body-'+this.value).focus();
		$(".editor-page").val(this.value).trigger("chosen:updated");
	});

  iCMS.select('pid',"<?php echo $rs['pid']?trim($rs['pid']):0 ; ?>");
  iCMS.select('cid',"<?php echo $cid; ?>");
  iCMS.select('scid',"<?php echo trim($rs['scid']);?>");
  iCMS.select('status',"<?php echo $rs['status']; ?>");
  $('#inbox').click(function(){
    if($(this).prop("checked")){
      iCMS.select('status',"0");
    }else{
      iCMS.select('status',"1");
    }
  })

  $('#ischapter').click(function(){
    var checkedStatus = $(this).prop("checked"),chapter = $("input[name=chapter]").val();
    subtitleToggle (checkedStatus);
    if(!checkedStatus && chapter>1){
      return confirm('您之前添加过其它章节!确定要取消章节模式?');
    }
  })
  var hotkey = false;

	$("#<?php echo APP_FORMID;?>").submit(function(){
    if(hotkey){
        if(this.action.indexOf('&keyCode=ctrl-s')===-1){
          this.action+='&keyCode=ctrl-s';
        }
    }

    var cid = $("#cid option:selected").val();
		if(cid=="0"){
      $("#cid").focus();
			iCMS.alert("请选择所属栏目");
			return false;
		}
		if($("#title").val()==''){
      $("#title").focus();
			iCMS.alert("标题不能为空!");
			return false;
		}
		if($("#url").val()==''){
			var n=$(".editor-page:eq(0) option:first").val(),ed = iCMS.editor.get('editor-body-'+n);
			if(!ed.hasContents()){
        ed.focus();
				iCMS.alert("第"+n+"页内容不能为空!");
				$('#editor-wrap-'+n).show();
				$(".editor-page").val(n).trigger("chosen:updated");
				return false;
			}
		}
    // if($('#ischapter').prop("checked") && $("#subtitle").val()==''){
    //   $("#subtitle").focus();
    //   iCMS.alert("章节模式下 章节标题不能为空!");
    //   return false;
    // }
	});
  $(document).keydown(function (e) {
    var keyCode = e.keyCode || e.which || e.charCode;
    var ctrlKey = e.ctrlKey || e.metaKey;
    if(ctrlKey && keyCode == 83) {
        e.preventDefault();
        hotkey = true;
        $("#<?php echo APP_FORMID;?>").submit();
    }
    hotkey = false;
  });
});

function mergeEditorPage(){
  var html = [];
  $(".editor-container").each(function(n,a){
    var eid = a.id.replace('editor-wrap-','editor-body-');
    if(iCMS.editor.container[eid]){
        iCMS.editor.container[eid].destroy();
    }
    var content = $("textarea",this).val();
    content && html.push(content);
    if(n){
        $(this).remove();
    }
  });

  $(".editor-container").show();
  var allHtml = html.join('#--iCMS.PageBreak--#'),
  ned = $("textarea",".editor-container"),
  neid = $(".editor-container").attr('id').replace('editor-wrap-','editor-body-');
  ned.val(allHtml).css({
    width: "100%",
    height: '500px'
  });
  iCMS.editor.create(neid).focus();
  $(".editor-page").html('<option value="1">第 1 页</option>').val(1).trigger("chosen:updated");
}
function addEditorPage(){
	//iCMSed.cleanup(iCMSed.id);
	var index	= parseInt($(".editor-page option:last").val()),n	= index+1;
	$(".editor-container").hide();
	$("#editor-wrap-"+index).after(
    '<div id="editor-wrap-'+n+'" class="editor-container">'+
      '<div class="chapter-title hide">'+
        '<input name="adid[]" id="adid-'+n+'" type="hidden" value="" />'+
        '<div class="input-prepend"> <span class="add-on" style="width:60px;">章节标题</span>'+
            '<input type="text"  id="chapter-title-'+n+'" disabled="true" name="chaptertitle[]" class="span6" value="" />'+
        '</div>'+
        '<div class="clearfloat mb10"></div>'+
      '</div>'+
      '<textarea type="text/plain" id="editor-body-'+n+'" name="body[]"></textarea>'+
    '</div>'
  );
	$(".editor-page").append('<option value="'+n+'">第 '+n+' 页</option>').val(n).trigger("chosen:updated");
	iCMS.editor.create('editor-body-'+n).focus();
  var checkedStatus = $('#ischapter').prop("checked");
  subtitleToggle (checkedStatus);
}
function subtitleToggle (checkedStatus) {
  if(checkedStatus){
    $(".subtitle-box").hide();
    $("input",".subtitle-box").attr("disabled","disabled");
    $(".chapter-title").show();
    $("input",".chapter-title").removeAttr("disabled");
  }else{
    $(".subtitle-box").show();
    $("input",".subtitle-box").removeAttr("disabled");
    $(".chapter-title").hide();
    $("input",".chapter-title").attr("disabled","disabled");
  }
}
function delEditorPage(){
	if($(".editor-page:eq(0) option").length==1) return;

	var s = $(".editor-page option:selected"),
    i = s.val(),p = s.prev(),n = s.next();
	if(n.length){
    var index = n.val();
	}else if(p.length){
    var index = p.val();
	}
  s.remove();
  iCMS.editor.destroy('editor-body-'+i);
  $("#editor-body-"+i).remove();
  $("#editor-wrap-"+i).remove();

	$(".editor-page").val(index).trigger("chosen:updated");
	$("#editor-wrap-"+index).show();
	iCMS.editor.eid	= 'editor-body-'+index;
	iCMS.editor.get('editor-body-'+index).focus();
}
function modal_picture(el,a){
  if(!a.checked) return;
  var ed = iCMS.editor.get(),
  url = $(a).attr("url");
  // if(a.checked){
  var imgObj  = {};
  imgObj.src  = url;
  imgObj._src = url;
	ed.fireEvent('beforeInsertImage', imgObj);
	ed.execCommand("insertImage", imgObj);
  _modal_dialog("继续选择");
  // }else{
  //   var html = ed.getContent(),
  //   img = '<img src="'+url+'"/>';

  //   html = html.replace(img,'');
  //   log(html);
  // }
	return true;
}
function modal_sweditor(el){
  if(!el.checked) return;

  var e    = $(el),
  image    = e.attr('_image'),
  fileType = e.attr('_fileType'),
  original = e.attr('_original'),
  url      = e.attr('url'),
  ed       = iCMS.editor.get();

  if(url=='undefined') return;
  var html = '<p class="attachment icon_'+fileType+'"><a href="'+url+'" target="_blank">' + original + '</a></p>';

  if(image=="1") html='<p><img src="'+url+'" /></p>';

	ed.execCommand("insertHTML",html);
  _modal_dialog("继续上传");
}
function _modal_dialog(cancel_text){
  iCMS.dialog({
      content:'插入成功!',
      okValue: '完成',
      ok: function () {
        window.iCMS_MODAL.destroy();
        return true;
      },
      cancelValue: cancel_text,
      cancel: function(){
        return true;
      }
  });
}
</script>

<div class="iCMS-container">
  <div class="widget-box">
    <div class="widget-title"> <span class="icon"> <i class="fa fa-pencil"></i> </span>
      <h5 class="brs"><?php echo empty($this->id)?'添加':'修改' ; ?>文章</h5>
      <ul class="nav nav-tabs" id="article-add-tab">
        <li class="active"><a href="#article-add-base" data-toggle="tab"><i class="fa fa-info-circle"></i> 基本信息</a></li>
        <li><a href="#article-add-publish" data-toggle="tab"><i class="fa fa-rocket"></i> 发布设置</a></li>
        <li><a href="#article-add-custom" data-toggle="tab"><i class="fa fa-wrench"></i> 自定义</a></li>
        <li><a href="#apps-metadata" data-toggle="tab"><i class="fa fa-sitemap"></i> 动态属性</a></li>
      </ul>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo APP_FURI; ?>&do=save" method="post" class="form-inline" id="<?php echo APP_FORMID;?>" target="iPHP_FRAME">
        <input name="_cid" type="hidden" value="<?php echo $rs['cid'] ; ?>" />
        <input name="_scid" type="hidden" value="<?php echo $rs['scid']; ?>" />
        <input name="_tags" type="hidden" value="<?php echo $rs['tags']; ?>" />
        <input name="_pid" type="hidden" value="<?php echo $rs['pid']; ?>" />

        <input name="aid" type="hidden" value="<?php echo $this->id ; ?>" />
        <input name="userid" type="hidden" value="<?php echo $rs['userid'] ; ?>" />
        <input name="postype" type="hidden" value="<?php echo $rs['postype'] ; ?>" />
        <input name="REFERER" type="hidden" value="<?php echo iPHP_REFERER ; ?>" />
        <input name="chapter" type="hidden" value="<?php echo $rs['chapter']; ?>" />
        <input name="markdown" type="hidden" value="<?php echo self::$config['markdown']; ?>" />
        <div id="article-add" class="tab-content">
          <div id="article-add-base" class="tab-pane active">
            <div class="input-prepend"> <span class="add-on">栏 目</span>
              <select name="cid" id="cid" class="chosen-select span3" data-placeholder="== 请选择所属栏目 ==">
                <?php echo $cata_option;?>
              </select>
            </div>
            <div class="input-prepend"> <span class="add-on">状 态</span>
              <select name="status" id="status" class="chosen-select span3">
                <option value="0"> 草稿 [status='0']</option>
                <option value="1"> 正常 [status='1']</option>
                <option value="2"> 回收站 [status='2']</option>
                <option value="3"> 待审核 [status='3']</option>
                <option value="4"> 未通过 [status='4']</option>
                <?php echo propAdmincp::get("status") ; ?>
              </select>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">副栏目</span>
              <select name="scid[]" id="scid" class="chosen-select span6" multiple="multiple"  data-placeholder="请选择副栏目(可多选)...">
                <?php echo $cata_option;?>
              </select>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">属 性</span>
              <select name="pid[]" id="pid" class="chosen-select span6" multiple="multiple">
                <option value="0">普通文章[pid='0']</option>
                <?php echo propAdmincp::get("pid") ; ?>
              </select>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">标 题</span>
              <input type="text" name="title" class="span6" id="title" value="<?php echo $rs['title'] ; ?>"/>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">短标题</span>
              <input type="text" name="stitle" class="span6" id="stitle" value="<?php echo $rs['stitle'] ; ?>"/>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend input-append"> <span class="add-on">出 处</span>
              <input type="text" name="source" class="span2" id="source" value="<?php echo $rs['source'] ; ?>"/>
              <?php echo propAdmincp::btn_group("source");?>
            </div>
            <div class="input-prepend input-append"> <span class="add-on">作 者</span>
              <input type="text" name="author" class="span2" id="author" value="<?php echo $rs['author'] ; ?>"/>
              <?php echo propAdmincp::btn_group("author");?>
            </div>
            <div class="input-prepend"> <span class="add-on">编 辑</span>
              <input type="text" name="editor" class="span2" id="editor" value="<?php echo $rs['editor'] ; ?>"/>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend input-append"> <span class="add-on">缩略图</span>
              <input type="text" name="pic" class="span6" id="pic" value="<?php echo $rs['pic'] ; ?>"/>
              <?php filesAdmincp::pic_btn("pic",$this->id);?>
              <span class="add-on" title="远程文件不执行本地化"><input type="checkbox" name="pic_http"/> <s>http</s></span>
              <script>
              <?php if(iFS::checkHttp($rs['pic'])){ ?>
                $('[name="pic_http"]').prop('checked','checked');
              <?php } ?>
              </script>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend input-append"> <span class="add-on">缩略图2</span>
              <input type="text" name="mpic" class="span6" id="mpic" value="<?php echo $rs['mpic'] ; ?>"/>
              <?php filesAdmincp::pic_btn("mpic",$this->id);?>
              <span class="add-on" title="远程文件不执行本地化"><input type="checkbox" name="mpic_http" /> <s>http</s></span>
              <script>
              <?php if(iFS::checkHttp($rs['mpic'])){ ?>
                $('[name="mpic_http"]').prop('checked','checked');
              <?php } ?>
              </script>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend input-append"> <span class="add-on">缩略图3</span>
              <input type="text" name="spic" class="span6" id="spic" value="<?php echo $rs['spic'] ; ?>"/>
              <?php filesAdmincp::pic_btn("spic",$this->id);?>
              <span class="add-on" title="远程文件不执行本地化"><input type="checkbox" name="spic_http"/> <s>http</s></span>
              <script>
              <?php if(iFS::checkHttp($rs['spic'])){ ?>
                $('[name="spic_http"]').prop('checked','checked');
              <?php } ?>
              </script>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">关键字</span>
              <input type="text" name="keywords" class="span6" id="keywords" value="<?php echo $rs['keywords'] ; ?>" onkeyup="javascript:this.value=this.value.replace(/，/ig,',');"/>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">标 签</span>
              <input type="text" name="tags" class="span6" id="tags" value="<?php echo $rs['tags'] ; ?>" onkeyup="javascript:this.value=this.value.replace(/，/ig,',');"/>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend" style="width:100%;"><span class="add-on">摘 要</span>
              <textarea name="description" id="description" class="span6" style="height: 150px;"><?php echo $rs['description'] ; ?></textarea>
            </div>
            <div class="clearfloat mb10"></div>
            <?php if(!$rs['chapter']){?>
            <div class="subtitle-box">
              <input name="adid" type="hidden" value="<?php echo $adRs['id']; ?>" />
              <div class="input-prepend "> <span class="add-on">副标题</span>
                  <input type="text" name="subtitle" class="span6" id="subtitle" value="<?php echo $adRs['subtitle'] ; ?>" />
              </div>
              <div class="clearfloat mb10"></div>
            </div>
            <?php } ?>
              <button class="btn btn-primary" type="submit"><i class="fa fa-check"></i> 提交</button>
              <div class="input-prepend">
                <span class="add-on"><i class="fa fa-building-o"></i> 内容</span>
                <select class="editor-page chosen-select">
                <?php
                  $option ='';
                  for($i=0;$i<$bodyCount;$i++){
                    $idNum  = $i+1;
                    $option .= '<option value="'.$idNum.'">第 '.$idNum.' / '.$bodyCount.' 页</option>';
                  }
                  echo $option;
                ?>
                </select>
              </div>
              <div class="input-prepend">
                <div class="btn-group">
                  <button type="button" class="btn" onclick="javascript:addEditorPage();"><i class="fa fa-file-o"></i> 新增一页</button>
                  <button type="button" class="btn" onclick="javascript:delEditorPage();"><i class="fa fa-times-circle"></i> 删除当前页</button>
                  <button type="button" class="btn" onclick="javascript:mergeEditorPage();"><i class="fa fa-align-justify"></i> 合并编辑</button>
                  <button type="button" class="btn" onclick="javascript:iCMS.editor.insPageBreak();"><i class="fa fa-ellipsis-h"></i> 插入分页符</button>
                  <button type="button" class="btn" onclick="javascript:iCMS.editor.delPageBreakflag();"><i class="fa fa-ban"></i> 删除分页符</button>
                  <button type="button" class="btn" onclick="javascript:iCMS.editor.cleanup();"><i class="fa fa-magic"></i> 自动排版</button>
                </div>
              </div>
              <!--div class="btn-group">
                <button type="button" class="btn" href="<?php echo __ADMINCP__; ?>=files&do=multi&from=modal&callback=sweditor" data-toggle="modal" title="批量上传"><i class="fa fa-upload"></i> 批量上传</button>
                <button type="button" class="btn" href="<?php echo __ADMINCP__; ?>=files&do=picture&from=modal&click=file&callback=picture" data-toggle="modal" title="从网站选择图片"><i class="fa fa-picture-o"></i> 从网站选择</button>
              </div-->
            <div class="clearfloat mb10"></div>
            <div class="input-prepend input-append">
              <span class="add-on wauto">
              <input name="ischapter" type="checkbox" id="ischapter" value="1" <?php if($rs['chapter']) echo 'checked="checked"'  ?>/>
              章节模式</span>
              <span class="add-on wauto">
              <input name="remote" type="checkbox" id="remote" value="1" <?php if(self::$config['remote']=="1")echo 'checked="checked"'  ?>/>
              下载远程图片</span>
              <span class="add-on wauto">
              <input name="autopic" type="checkbox" id="autopic" value="1" <?php if(self::$config['autopic']=="1")echo 'checked="checked"'  ?>/>
              提取缩略图 </span>
              <span class="add-on wauto">
              <input name="dellink" type="checkbox" id="dellink" value="1"/>
              清除链接
              </span>
              <?php if(iCMS::$config['watermark']['enable']=="1"){ ?>
              <span class="add-on wauto">
                <input name="iswatermark" type="checkbox" id="iswatermark" value="1" />不添加水印
              </span>
              <?php }?>
              <a class="btn tip-top" href="<?php echo buildurl(null,array('ui_editor'=>'markdown')); ?>" title="请先保存数据"><i class="fa fa-edit"></i> 切换到markdown编辑器</a>
            </div>
            <div class="clearfloat mb10"></div>
            <?php for($i=0;$i<$bodyCount;$i++){
                $idNum  = $i+1;
            ?>
            <div id="editor-wrap-<?php echo $idNum;?>" class="editor-container<?php if($i){ echo ' hide';}?>">
              <div class="chapter-title <?php if(!$rs['chapter']){ echo ' hide';}?>">
                <input name="adid[]" id="adid-<?php echo $idNum;?>" <?php if(!$rs['chapter']){ echo ' disabled="true"';}?> type="hidden" value="<?php echo $adIdArray[$i] ; ?>" />
                <div class="input-prepend">
                  <span class="add-on" style="width:60px;">章节标题</span>
                  <input type="text" id="chapter-title-<?php echo $idNum;?>" <?php if(!$rs['chapter']){ echo ' disabled="true"';}?> name="chaptertitle[]" class="span6" value="<?php echo $cTitArray[$i] ; ?>" />
                </div>
                <div class="clearfloat mb10"></div>
              </div>
              <textarea type="text/plain" id="editor-body-<?php echo $idNum;?>" name="body[]"><?php echo $bodyArray[$i];?></textarea>
            </div>
            <?php }?>

            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on"><i class="fa fa-building-o"></i> 内容</span>
              <select class="editor-page chosen-select">
              <?php echo $option;?>
              </select>
            </div>
            <div class="input-prepend">
              <div class="btn-group">
                <button type="button" class="btn" onclick="javascript:addEditorPage();"><i class="fa fa-file-o"></i> 新增一页</button>
                <button type="button" class="btn" onclick="javascript:delEditorPage();"><i class="fa fa-times-circle"></i> 删除当前页</button>
                <button type="button" class="btn" onclick="javascript:mergeEditorPage();"><i class="fa fa-align-justify"></i> 合并分页</button>
                <button type="button" class="btn" onclick="javascript:iCMS.editor.insPageBreak();"><i class="fa fa-ellipsis-h"></i> 插入分页符</button>
                <button type="button" class="btn" onclick="javascript:iCMS.editor.delPageBreakflag();"><i class="fa fa-ban"></i> 删除分页符</button>
                <button type="button" class="btn" onclick="javascript:iCMS.editor.cleanup();"><i class="fa fa-magic"></i> 自动排版</button>
              </div>
            </div>
          </div>
          <div id="article-add-publish" class="tab-pane hide">
            <div class="input-prepend"> <span class="add-on">发布时间</span>
              <input id="pubdate" class="<?php echo $readonly?'':'ui-datepicker'; ?>" value="<?php echo $rs['pubdate']?$rs['pubdate']:get_date(0,'Y-m-d H:i:s') ; ?>"  name="pubdate" type="text" style="width:230px" <?php echo $readonly ; ?>/>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">排序</span>
              <input id="sortnum" class="span2" value="<?php echo $rs['sortnum']?$rs['sortnum']:time() ; ?>" name="sortnum" type="text"/>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">权重</span>
              <input id="weight" class="span2" value="<?php echo $rs['weight']?$rs['weight']:time(); ?>" name="weight" type="text"/>
            </div>
            <div class="clearfix mb10"></div>
            <div class="input-prepend input-append">
              <span class="add-on">总点击数</span>
              <input type="text" name="hits" class="span1" id="hits" value="<?php echo $rs['hits']?$rs['hits']:'0'; ?>"/>
              <span class="add-on">当天点击数</span>
              <input type="text" name="hits_today" class="span1" id="hits_today" value="<?php echo $rs['hits_today']?$rs['hits_today']:'0'; ?>"/>
              <span class="add-on">昨天点击数</span>
              <input type="text" name="hits_yday" class="span1" id="hits_yday" value="<?php echo $rs['hits_yday']?$rs['hits_yday']:'0'; ?>"/>
              <span class="add-on">周点击</span>
              <input type="text" name="hits_week" class="span1" id="hits_week" value="<?php echo $rs['hits_week']?$rs['hits_week']:'0'; ?>"/>
              <span class="add-on">月点击</span>
              <input type="text" name="hits_month" class="span1" id="hits_month" value="<?php echo $rs['hits_month']?$rs['hits_month']:'0'; ?>"/>
            </div>
            <div class="clearfix mb10"></div>
            <div class="input-prepend input-append">
              <span class="add-on">收藏数</span>
              <input type="text" name="favorite" class="span1" id="favorite" value="<?php echo $rs['favorite']?$rs['favorite']:'0'; ?>"/>
              <span class="add-on">评论数</span>
              <input type="text" name="comments" class="span1" id="comments" value="<?php echo $rs['comments']?$rs['comments']:'0'; ?>"/>
              <span class="add-on">点赞数</span>
              <input type="text" name="good" class="span1" id="good" value="<?php echo $rs['good']?$rs['good']:'0'; ?>"/>
              <span class="add-on">点踩数</span>
              <input type="text" name="bad" class="span1" id="bad" value="<?php echo $rs['bad']?$rs['bad']:'0'; ?>"/>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend input-append"> <span class="add-on">模板</span>
              <input type="text" name="tpl" class="span6" id="tpl" value="<?php echo $rs['tpl'] ; ?>"/>
              <?php echo filesAdmincp::modal_btn('模板','tpl');?>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">自定链接</span>
              <input type="text" name="clink" class="span6" id="clink" value="<?php echo $rs['clink'] ; ?>"/>
              <span class="help-inline">只能由英文字母、数字或_-组成(不支持中文),留空则自动以标题拼音填充</span> </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">外部链接</span>
              <input type="text" name="url" class="span6 tip" title="注意:文章设置外部链接后编辑器里的内容是不会被保存的哦!" id="url" value="<?php echo $rs['url'] ; ?>"/>
               </div><span class="help-inline">不填写请留空!</span>
            <div class="clearfloat mb10"></div>
          </div>
          <div id="article-add-custom" class="tab-pane hide">
            <?php echo former::layout();?>
          </div>
          <div id="apps-metadata" class="tab-pane hide">
            <script>
            $("#cid").on('change', function() {
              get_category_meta(this.value,"#apps-metadata");
            });
            </script>
            <?php include admincp::view("apps.meta","apps");?>
          </div>
        </div>
        <div class="form-actions">
          <button class="btn btn-primary" type="submit"><i class="fa fa-check"></i> 提交</button>
        </div>
      </form>
    </div>
  </div>
</div>
<div id="category_select" style="display: none;">
    <input class="form-control input-lg" id="user_category_new" type="text" placeholder="请输入分类名称">
</div>
<?php admincp::foot();?>
