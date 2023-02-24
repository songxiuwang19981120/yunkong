<?php/*普通分页类*/namespace org;class Page{    // 前渐减的页数导航总数    public $BeforePages = 5;    // 后渐加的页数导航总数    public $AfterPages = 5;    // 页数跳转时要带的参数    public $parameter = '';    // 默认列表每页显示行数    public $listRows = 5;    // 起始行数    public $firstRow;    // 分页总页面数    protected $totalPages;    // 总行数    protected $totalRows;    // 当前页数    protected $nowPage;    // 分页的栏的总页数    protected $coolPages;    // 分页显示定制    protected $config = array('first' => '首页', 'prev' => '上一页', 'next' => '下一页', 'last' => '末页', 'theme' => '%first% %upPage%  %linkPage%  %downPage% %end%');    // 默认分页变量名    protected $varPage;    /**     * 架构函数     * @access public     * @param array $totalRows 总的记录数     * @param array $listRows 每页显示记录数     * @param array $parameter 分页跳转的参数     */    public function __construct($totalRows, $listRows = '', $parameter = '')    {        $this->totalRows = $totalRows; // 构造函数参数 1，总页数        $this->parameter = $parameter; // 构造函数参数 3，URL 附加参数        $this->varPage = config('VAR_PAGE') ? config('VAR_PAGE') : 'p'; // 获取分页变量名，如果未定义则定义默认分页变量名        /**         * intval() 将变量转成整数类型         */        if (!empty($listRows)) { // 构造函数参数 2，获取每页显示的条数，如果每页显示的条数不为空则            $this->listRows = intval($listRows); // 转换为整型并赋值给每页显示的条数        }        /**         * ceil() 函数向上舍入为最接近的整数(1.1=2)         */        $this->totalPages = ceil($this->totalRows / $this->listRows); // 获取总页数，记录集的总数除以每页显示的条数等于总页数        // 假设有 40 条数据，每页显示 5 条，就是有 8 页，每个页面显示 2 个导航栏，就是有4栏        //$this->coolPages    =   ceil($this->totalPages/$this->AfterPages); // 获取总栏数， 总栏数除以每页显示的栏数等于总栏数        /**         * empty() 如果参数是非空或非零的值，则返回 FALSE,否则返回 TRUE         */        $this->nowPage = !empty(input('param.p')) ? intval(input('param.p')) : 1; // 获取当前页数，如果 URL 当前页数参数不为空则转换整型并赋值给当前页数，否则赋值为 1        if (!empty($this->totalPages) && $this->nowPage > $this->totalPages) { // 如果总页数不为空并且当前页数大于总页数则            $this->nowPage = $this->totalPages; // 赋值当前页数为总页数        }        // 假设当前页数为 2，每页显示 5 条数据，当前页面就是从第 (5*(2-1)=5) 条记录开始读取数据,        // 根据 limit 函数定义，索引从零开始，也就是实际的值是记录集的第六条数据        $this->firstRow = $this->listRows * ($this->nowPage - 1); // 获取起始页，起始行数等于每页显示的条数乘以当前页面减 1    }    /**     * 自定义导航显示     * @access public     * @param String $name 待替换的参数名称     * @param String $value 替换的参数值     * isset() 返回  bool 值     * 若变量不存在则返回 FALSE     * 若变量存在且其值为NULL，也返回 FALSE     * 若变量存在且值不为NULL，则返回 TURE     */    public function setConfig($name, $value)    {        if (isset($this->config[$name])) {            $this->config[$name] = $value;        }    }    /**     * 分页显示输出     * @access public     * @author lanfengye <zibin_5257@163.com>     */    public function show()    {        if (0 == $this->totalRows) return '';        $p = $this->varPage; // 默认分页变量名        //获取控制器名和方法名，并判断是否url不区分大小写        $url_case = true;        $module = app('http')->getName();        $controller_name = request()->controller();        $action_name = request()->action();        //替换附加参数中的分隔符        $parameter = $this->parameter;        foreach ($parameter as $key => $val) {            if (empty($val)) {                unset($parameter[$key]);            }        }        //上翻页字符串        $upRow = $this->nowPage - 1;        $theFirstRow = 1;        if ($upRow > 0) {            $theFirst = "<a class=\"page-numbers\" href='" . url($module . '/' . $controller_name . '/' . $action_name, array_merge($parameter, ['p' => $theFirstRow])) . "'>" . $this->config['first'] . "</a>";            $upPage = "<a class=\"page-numbers\" href='" . url($module . '/' . $controller_name . '/' . $action_name, array_merge($parameter, ['p' => $upRow])) . "'>" . $this->config['prev'] . "</a>";        } else {            $theFirst = "<a class=\"page-numbers\" href='#'>" . $this->config['first'] . "</a>";            $upPage = "<a class=\"page-numbers\" href='#'>" . $this->config['prev'] . "</a>";        }        // 下翻页字符串        $downRow = $this->nowPage + 1;        $theEndRow = $this->totalPages;        if ($downRow <= $this->totalPages) {            $downPage = "<a class=\"page-numbers\" href='" . url($module . '/' . $controller_name . '/' . $action_name, array_merge($parameter, ['p' => $downRow])) . "'>" . $this->config['next'] . "</a>";            $theEnd = "<a class=\"page-numbers\" href='" . url($module . '/' . $controller_name . '/' . $action_name, array_merge($parameter, ['p' => $theEndRow])) . "'>" . $this->config['last'] . "</a>";        } else {            $downPage = "<a href='#' class=\"page-numbers\">" . $this->config['next'] . "</a>";            $theEnd = "<a href='#' class=\"page-numbers\">" . $this->config['last'] . "</a>";        }        // 后置导航        $AfterPage = $this->nowPage - 1;        if ($AfterPage >= 0) {            $this->AfterPages += $AfterPage;        }        $j = '';        if ($AfterPage > $this->totalPages) $AfterPage = $this->totalPages;        $nowCoolPage = ceil($this->nowPage / ($this->AfterPages));        // 前置导航                if ($this->nowPage > 5) {            $BeforePage = $this->nowPage - $this->BeforePages;            $j = $BeforePage;        } else {            $j = 1;        }        if ($j < 0) $j = 1;        // 1 2 3 4 5                $linkPage = "";        for (; $j <= $this->AfterPages; $j++) {            $page = ($nowCoolPage - 1) * ($this->AfterPages - $AfterPage) + $j;            if ($page != $this->nowPage) {                if ($page <= $this->totalPages) {                    if ($j % 2 == 0) {                        $linkPage .= "<a class=\"page-numbers\" href='" . url($module . '/' . $controller_name . '/' . $action_name, array_merge($parameter, ['p' => $page])) . "'>" . $page . "</a>";                    } else {                        $linkPage .= "<a class=\"page-numbers\" href='" . url($module . '/' . $controller_name . '/' . $action_name, array_merge($parameter, ['p' => $page])) . "'>" . $page . "</a>";                    }                } else {                    break;                }            } else {                if ($this->totalPages != 1) {                    $linkPage .= "<a class=\"page-numbers actiive\" href='#'>" . $page . "</a>";                }            }        }        $pageStr = str_replace(            array('%nowPage%', '%totalRow%', '%totalPage%', '%upPage%', '%downPage%', '%first%', '%linkPage%', '%end%'),            array($this->nowPage, $this->totalRows, $this->totalPages, $upPage, $downPage, $theFirst, $linkPage, $theEnd), $this->config['theme']);        return $pageStr;    }}