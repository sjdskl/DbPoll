<?php
/**
 * Created by PhpStorm.
 * User: kailishen
 * Date: 2018/3/5
 * Time: 下午4:36
 */


$cc = [];
if(!$cc) {
    echo 1;
}



class a
{
    public $_stack;
    private $_deepest_stack;

    public function aa()
    {
        echo 2222 . "\n";
    }

    public function __call($name, $arguments)
    {
        $stack['medtho'] = $name;
        $stack['params'] = $arguments;
        $stack['results'] = '';
        if(!$this->_stack) {
            $this->_stack = $stack;
            $this->_deepest_stack = &$this->_stack;
        } else {
            $this->_deepest_stack['results'] = $stack;
            $this->_deepest_stack = &$this->_deepest_stack['results'];
        }

        return $this;
    }

}


$a = new a();

$a->b(1,2,3)->c([1])->d()->f('sdf')->aa();

print_r($a->_stack);