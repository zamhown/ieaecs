var oldData = '';
var trCount = 0;
var $activeTr;
var $activeLabelContainer;
var modalOpened = false;
var modalAgree, modalDisagree, modalUncertain;
var $modalAgree, $modalDisagree, $modalUncertain;

$(function(){
    document.onkeydown = keyDown;

    $('#url').html(url);

    oldData = $('p.data').html();
    trCount = $('tr.trData').length;

    showKeywords(oldData);
    refreshLabelState();

    $('a.label').mouseenter(function(e){
        $('a.label').removeClass('hover');
        $(e.target).addClass('hover');
    })
    $('a.label').mouseleave(function(e){
        $('a.label').removeClass('hover');
    })

    // 默认第一行数据源选中
    switchDataSource($('tr.trData').eq(0));

    $modalAgree = $('[data-remodal-id=modalAgree]');
    $modalDisagree = $('[data-remodal-id=modalDisagree]');
    $modalUncertain = $('[data-remodal-id=modalUncertain]');

    modalAgree = $modalAgree.remodal();
    modalDisagree = $modalDisagree.remodal();
    modalUncertain = $modalUncertain.remodal();

    $(document).on('opened', '[data-remodal-id=modalAgree]', function () {
        $activeLabelContainer = $modalAgree;
    });
    $(document).on('opened', '[data-remodal-id=modalDisagree]', function () {
        $activeLabelContainer = $modalDisagree;
    });
    $(document).on('opened', '[data-remodal-id=modalUncertain]', function () {
        $activeLabelContainer = $modalUncertain;
    });
    $(document).on('opened', '.remodal', function (e) {
        modalOpened = true;
    });
    $(document).on('closed', '.remodal', function (e) {
        $activeLabelContainer = $activeTr;
        modalOpened = false;
        // 刷新标签点击状态
        refreshLabelState();
    });
});

function showKeywords(data){
    data = data.replace(new RegExp(kwreg, 'gm'), s => '<span class="keyword">'+s+'</span>');
    $('p.data').html(data);
    return data;
}

function refreshLabelState(){
    $('tr.trData').each(function(){
        var $this = $(this);
        $this.find('a.label').removeClass('checked');
        var labelId = $this.find('input.label-v').val() * 1;
        if(labelDic[labelId]==1){
            $this.find('a.agree').addClass('checked');
        }else if(labelDic[labelId]==2){
            $this.find('a.disagree').addClass('checked');
        }else if(labelDic[labelId]==3){
            $this.find('a.uncertain').addClass('checked');
        }
    });
}

function openModal($modal, rid, labelId){
    $modal.find('a.label').attr('rid', rid).removeClass('checked');
    $modal.find('a.label[value='+labelId+']').addClass('checked');
    $modal.remodal().open();
}

function closeModal(){
    modalAgree.close();
    modalDisagree.close();
    modalUncertain.close();
}

function clickLabel(e){
    e.preventDefault();
    selectLabel($(e.target));
}

function selectLabel($a){
    var rid = $a.attr('rid');
    var type = $a.attr('type');

    var $tr = $('#tr'+rid);
    $tr.find('a.label').removeClass('checked');
    var labelId = $tr.find('input.label-v').val() * 1;
    if(type==1){
        if(agreeLabelIds.length>1){
            openModal($modalAgree, rid, labelId);
        }else if(agreeLabelIds.length==1){
            $tr.find('input.label-v').val(agreeLabelIds[0]);
            $tr.find('a.agree').addClass('checked');
        }
    }else if(type==2){
        if(disagreeLabelIds.length>1){
            openModal($modalDisagree, rid, labelId);
        }else if(disagreeLabelIds.length==1){
            $tr.find('input.label-v').val(disagreeLabelIds[0]);
            $tr.find('a.disagree').addClass('checked');
        }
    }else if(type==3){
        if(uncertainLabelIds.length>1){
            openModal($modalUncertain, rid, labelId);
        }else if(uncertainLabelIds.length==1){
            $tr.find('input.label-v').val(uncertainLabelIds[0]);
            $tr.find('a.uncertain').addClass('checked');
        }
    }
}

function clickSubLabel(e){
    e.preventDefault();
    selectSubLabel($(e.target));
}

function selectSubLabel($a){
    $('.remodal a.label').removeClass('checked');
    $a.addClass('checked');
    var rid = $a.attr('rid');
    var value = $a.attr('value');
    if(rid*1>0){
        $('#tr'+rid).find('input.label-v').val(value);
    }else{
        $('tr.trData').each(function(){
            $(this).find('input.label-v').val(value);
        });
    }
    closeModal();
}

function clickAllDisagree(e){
    e.preventDefault();
    // 查找有没有统一标签
    var li = -1;
    $('tr.trData').each(function(){
        var v = $(this).find('input.label-v').val() * 1;
        if(li>=0 && li!=v){
            li = 0;
        }else{
            li = v;
        }
    });
    openModal($modalDisagree, 0, li);
}

function ome(e){
    switchDataSource($(e.target));
}

function switchDataSource($tr){
    var s = $tr.find('td.tdData .data').html();
    showKeywords(oldData.replace(new RegExp(s, 'gm'), '<span class="result">'+s+'</span>'));

    $('tr').removeClass('active');
    $tr.addClass('active');

    $activeTr = $tr;
    $activeLabelContainer = $tr;
}

function switchLabel(index){
    $('a.label').removeClass('hover');
    $activeLabelContainer.find('a.label').eq(index).addClass('hover');
}

function keyDown(e){
    var currKey=0,e=e||event;
    currKey=e.keyCode||e.which||e.charCode;
    if(currKey==37 /*左*/){
        if(!modalOpened){
            if(!$('tr.trData.active').length){
                switchDataSource($('tr.trData').last());
            }else{
                switchDataSource($('tr.trData').eq(($activeTr.index()-1)%trCount));
            }
        }
    }else if(currKey==39 /*右*/){
        if(!modalOpened){
            if(!$('tr.trData.active').length){
                switchDataSource($('tr.trData').first());
            }else{
                switchDataSource($('tr.trData').eq(($activeTr.index()+1)%trCount));
            }
        }
    }else if(currKey==38 /*上*/){
        e.preventDefault();
        if(!modalOpened && !$('tr.trData.active').length){
            switchDataSource($('tr.trData').first());
        }
        var rc = $activeLabelContainer.find('a.label.hover');
        if(!rc.length){
            rc = $activeLabelContainer.find('a.label.checked');
        }
        var labelCount = $activeLabelContainer.find('a.label').length;
        if(rc.length){
            switchLabel((rc.index()-1)%labelCount);
        }else{
            switchLabel(labelCount-1);
        }
    }else if(currKey==40 /*下*/){
        e.preventDefault();
        if(!modalOpened && !$('tr.trData.active').length){
            switchDataSource($('tr.trData').first());
        }
        var rc = $activeLabelContainer.find('a.label.hover');
        if(!rc.length){
            rc = $activeLabelContainer.find('a.label.checked');
        }
        var labelCount = $activeLabelContainer.find('a.label').length;
        if(rc.length){
            switchLabel((rc.index()+1)%labelCount);
        }else{
            switchLabel(0);
        }
    }else if(currKey==13 /*回车*/){
        e.preventDefault();
        if($activeLabelContainer.find('a.label.hover').length){
            if(modalOpened){
                selectSubLabel($activeLabelContainer.find('a.label.hover'));
            }else{
                selectLabel($activeLabelContainer.find('a.label.hover'));
            }
        }else if($activeLabelContainer.find('a.label.checked').length){
            if(modalOpened){
                closeModal();
            }else{
                $('#judgeForm').submit();
            }
        }
    }
}