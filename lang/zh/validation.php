<?php
return [
    'accepted' => '请同意:attribute。', 'required' => '请填写:attribute。',
    'required_with' => '填写:values时也需要填写:attribute。', 'date' => ':attribute必须是有效日期。',
    'before_or_equal' => ':attribute不能晚于:date。', 'before' => ':attribute必须早于:date。',
    'numeric' => ':attribute必须是数字。', 'integer' => ':attribute必须是整数。',
    'max' => ['string'=>':attribute不能超过:max个字符。','file'=>':attribute不能超过:max KB。','array'=>':attribute不能超过:max项。'],
    'between' => ['numeric'=>':attribute必须介于:min和:max之间。'],
    'mimetypes' => ':attribute的文件类型不受支持。', 'mimes'=>':attribute的文件类型不受支持。',
    'image'=>':attribute必须是图片。', 'in'=>':attribute选项无效。', 'uuid'=>':attribute无效。',
    'attributes'=>['consent'=>'隐私告知','full_name'=>'姓名','last_seen_location'=>'最后出现地点','sighted_at'=>'目击时间','evidence'=>'证据','gender'=>'性别'],
];
