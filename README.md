### 安装yaf扩展
```ini
extension=yaf.so
;配置环境类型dev(开发状态)| product(开发环境)
yaf.environ = dev
;php扩展内目录.
yaf.library = "/data/www/xuan/yafLib"
;是否开启配置文件缓存 0:不缓存,1:缓存
yaf.cache_config = 0
;在处理Controller, Action, Plugin, Model的时候, 类名中关键信息是否是后缀式, 比如UserModel, 而在前缀模式下则是ModelUser (这里使用的是后缀)
yaf.name_suffix = 1
;在处理Controller, Action, Plugin, Model的时候, 前缀和名字之间的分隔符, 默认为空, 也就是UserPlugin, 加入设置为"_", 则判断的依据就会变成:"User_Plugin", 这个主要是为了兼容ST已有的命名规范
yaf.name_separator = ""
;forward最大嵌套深度
yaf.forward_limit = 5
;是否启用命名空间 1:启用
yaf.use_namespace = 1
;是否开启php加载功能 建议关闭.
yaf.use_spl_autoload = 0
```
