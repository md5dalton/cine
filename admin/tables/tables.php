<?php

namespace Tables;


class Directories extends Table
{
    
    use SingleInstanceTable;
    
    protected $columns = '
        id VARCHAR(255) PRIMARY KEY,    
        path VARCHAR(255),
        contenttype VARCHAR(10),
        cl INT DEFAULT 0,

        INDEX content_index (contenttype)
    ';
    
}

?>