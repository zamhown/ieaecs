<?php
  
function csv_to_array($csv)  
{  
    $len = strlen($csv);  
  
  
    $table = array();  
    $cur_row = array();  
    $cur_val = "";  
    $state = "first item";  
  
  
    for ($i = 0; $i < $len; $i++)  
    {  
        //sleep(1000);  
        $ch = substr($csv,$i,1);  
        if ($state == "first item")  
        {  
            if ($ch == '"') $state = "we're quoted hea";  
            elseif ($ch == ",") //empty  
            {  
                $cur_row[] = ""; //done with first one  
                $cur_val = "";  
                $state = "first item";  
            }  
            elseif ($ch == "\n")  
            {  
                $cur_row[] = $cur_val;  
                $table[] = $cur_row;  
                $cur_row = array();  
                $cur_val = "";  
                $state = "first item";  
            }  
            elseif ($ch == "\r") $state = "wait for a line feed, if so close out row!";  
            else  
            {  
                $cur_val .= $ch;  
                $state = "gather not quote";  
            }  
              
        }  
  
        elseif ($state == "we're quoted hea")  
        {  
            if ($ch == '"') $state = "potential end quote found";  
            else $cur_val .= $ch;  
        }  
        elseif ($state == "potential end quote found")  
        {  
            if ($ch == '"')  
            {  
                $cur_val .= '"';  
                $state = "we're quoted hea";  
            }  
            elseif ($ch == ',')  
            {  
                $cur_row[] = $cur_val;  
                $cur_val = "";  
                $state = "first item";  
            }  
            elseif ($ch == "\n")  
            {  
                $cur_row[] = $cur_val;  
                $table[] = $cur_row;  
                $cur_row = array();  
                $cur_val = "";  
                $state = "first item";  
            }  
            elseif ($ch == "\r") $state = "wait for a line feed, if so close out row!";  
            else  
            {  
                $cur_val .= $ch;  
                $state = "we're quoted hea";  
            }  
  
        }  
        elseif ($state == "wait for a line feed, if so close out row!")  
        {  
            if ($ch == "\n")  
            {  
                $cur_row[] = $cur_val;  
                $cur_val = "";  
                $table[] = $cur_row;  
                $cur_row = array();  
                $state = "first item";  
  
            }  
            else  
            {  
                $cur_row[] = $cur_val;  
                $table[] = $cur_row;  
                $cur_row = array();  
                $cur_val = $ch;  
                $state = "gather not quote";  
            }     
        }  
  
        elseif ($state == "gather not quote")  
        {  
            if ($ch == ",")  
            {  
                $cur_row[] = $cur_val;  
                $cur_val = "";  
                $state = "first item";  
                  
            }  
            elseif ($ch == "\n")  
            {  
                $cur_row[] = $cur_val;  
                $table[] = $cur_row;  
                $cur_row = array();  
                $cur_val = "";  
                $state = "first item";  
            }  
            elseif ($ch == "\r") $state = "wait for a line feed, if so close out row!";  
            else $cur_val .= $ch;  
        }  
  
    }  
  
    return $table;  
}  
  
//pass a csv string, get a php array  
// example:  
//$arr = csv_to_array(file_get_contents('user.csv'));  
//echo "<pre>"; print_r($arr);   echo "</pre>"  