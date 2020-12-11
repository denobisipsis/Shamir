<?
/*
http://point-at-infinity.org/ssss/
*/	 
class shamir_original
{
var $degree,$poly;

function __construct()
	{
	  $this->degree = 1024;
	  $this->poly = gmp_init(0);	
	}
				 
function to32($integer)
	{
	if (0xffffffff < $integer || -0xffffffff > $integer){
	            $integer = fmod($integer, 0xffffffff + 1);
	        }
        if (0x7fffffff < $integer){
            $integer -= 0xffffffff + 1.0;
        }elseif (-0x80000000 > $integer){
            $integer += 0xffffffff + 1.0;
        }		
	return $integer;		
	}
			
function _rshift($integer, $n)
	{
        $integer = $this->to32($integer);
	
            if (0 > $integer){
                $integer &= 0x7fffffff;         
                $integer >>= $n;                  
                $integer |= 1 << (31 - $n); 
            }else{
                $integer >>= $n;                    
            }
        return $integer;
    	}
   
 function decrypt($data) 
      {      
      $len = $this->degree/8;$delta = 2654435769;
      for($i = 40 * $len - 2; $i >= 0; $i -= 2)
        {								
	    for($j = 0; $j < 2; $j++)
	  	{   		    		  
		    $vv[$j] = ($data[($i + 4 * $j) % $len] << 24) +
		      ($data[($i + 4 * $j + 1) % $len] << 16) + 
		      ($data[($i + 4 * $j + 2) % $len] << 8) + 
		      ($data[($i + 4 * $j + 3) % $len]);
	        }
	    $sum = 3337565984 ;//0xC6EF3720;	    	    
	    for($j = 0; $j < 32; $j++) 
		    {
		      $vv[1] -= $this->to32(((($vv[0] << 4) ^ $this->_rshift($vv[0], 5)) + $vv[0])^ $sum);         
		      $sum   -= $delta;
		      $vv[0] -= $this->to32(((($vv[1] << 4) ^ $this->_rshift($vv[1], 5)) + $vv[1])^ $sum);
		    }		    
	   $vv[0]&= 4294967295;$vv[1]&=4294967295;
	   for($j = 0; $j < 2; $j++) 
		  {
		    $data[($i + 4 * $j + 0) % $len] = ($vv[$j] >> 24) ;
		    $data[($i + 4 * $j + 1) % $len] = ($vv[$j] >> 16) & 0xff;
		    $data[($i + 4 * $j + 2) % $len] = ($vv[$j] >> 8) & 0xff;
		    $data[($i + 4 * $j + 3) % $len] = $vv[$j] & 0xff;
		  }       	
        }	
      return array_reverse($data);
      }

function teadecrypt($secret)
	{
	  $loc1 = array_reverse(array_values(unpack("C*",gmp_export($secret))));
	  
	  $w=array();
	  for ($k = 0;$k<sizeof($loc1);$k +=2)
	  	{
	  	if (@$loc1[$k+1])
	  		{
	  		$w[$k]   = $loc1[$k+1];
	  		$w[$k+1] = $loc1[$k];
	  		}
	  	else $w[$k] = $loc1[$k];
	  	}
		  
	  $loc1 = $this->decrypt($w);
	  
	  $w=array();
	  $w[0] = $loc1[0];
	  for ($k = 1;$k<sizeof($loc1);$k +=2)
	  	{	  
	  	$w[$k]   = $loc1[$k+1];
	  	$w[$k+1] = $loc1[$k];	  
	  	}	  	
	  
	  $secret = "";
	  foreach ($w as $r) $secret.=chr($r);
	  return $secret;	
	}
	 
function field_init()
	{
	$irred_coeff = array(
	  4,3,1,5,3,1,4,3,1,7,3,2,5,4,3,5,3,2,7,4,2,4,3,1,10,9,3,9,4,2,7,6,2,10,9,
	  6,4,3,1,5,4,3,4,3,1,7,2,1,5,3,2,7,4,2,6,3,2,5,3,2,15,3,2,11,3,2,9,8,7,7,
	  2,1,5,3,2,9,3,1,7,3,1,9,8,3,9,4,2,8,5,3,15,14,10,10,5,2,9,6,2,9,3,2,9,5,
	  2,11,10,1,7,3,2,11,2,1,9,7,4,4,3,1,8,3,1,7,4,1,7,2,1,13,11,6,5,3,2,7,3,2,
	  8,7,5,12,3,2,13,10,6,5,3,2,5,3,2,9,5,2,9,7,2,13,4,3,4,3,1,11,6,4,18,9,6,
	  19,18,13,11,3,2,15,9,6,4,3,1,16,5,2,15,14,6,8,5,2,15,11,2,11,6,2,7,5,3,8,
	  3,1,19,16,9,11,9,6,15,7,6,13,4,3,14,13,3,13,6,3,9,5,2,19,13,6,19,10,3,11,
	  6,5,9,2,1,14,3,2,13,3,1,7,5,4,11,9,8,11,6,5,23,16,9,19,14,6,23,10,2,8,3,
	  2,5,4,3,9,6,4,4,3,2,13,8,6,13,11,1,13,10,3,11,6,5,19,17,4,15,14,7,13,9,6,
	  9,7,3,9,7,1,14,3,2,11,8,2,11,6,4,13,5,2,11,5,1,11,4,1,19,10,3,21,10,6,13,
	  3,1,15,7,5,19,18,10,7,5,3,12,7,2,7,5,1,14,9,6,10,3,2,15,13,12,12,11,9,16,
	  9,7,12,9,3,9,5,2,17,10,6,24,9,3,17,15,13,5,4,3,19,17,8,15,6,3,19,6,1);
	
	$poly = gmp_init(0);  
	gmp_setbit($poly, $this->degree);  
	gmp_setbit($poly, $irred_coeff[3 * ($this->degree / 8 - 1) + 0]);
	gmp_setbit($poly, $irred_coeff[3 * ($this->degree / 8 - 1) + 1]);
	gmp_setbit($poly, $irred_coeff[3 * ($this->degree / 8 - 1) + 2]);
	gmp_setbit($poly, 0);
	return $poly;
	}

function sizeinbits($A) 
	{
	$A = ltrim(bin2hex(gmp_export($A)),"0");
	$B = sprintf( "%04d", decbin(hexdec($A[0])));
	return strlen($A)*4-strpos($B,"1");
	}

function swap(&$a,&$b)
	{$c=$a;$a=$b;$b=$c;}

function field_invert($x)
	{
	  $v = $this->poly;
	  $g = gmp_init(0);
	  $z = gmp_init(1);
	
	  while ($x>1) 
		  {
		    $i = $this->sizeinbits($x) - $this->sizeinbits($v);
		    if ($i < 0){$this->swap($x, $v);$this->swap($z, $g);$i = -$i;}
		    $x = gmp_xor($x, $v<<$i);
		    $z = gmp_xor($z, $g<<$i);
		  }
	return $z;
	}

function field_mult($x, $y)
	{   	  
	  if (gmp_testbit($y, 0)) 	$z = $x;
	  else 				$z = 0;
	  
	  for($i = 1; $i < $this->degree; $i++) 
		  {
		    $x = gmp_mul($x, 2);
		    if (gmp_testbit($x, $this->degree))$x = gmp_xor($x, $this->poly);
		    if (gmp_testbit($y, $i)) 	       $z = gmp_xor($z, $x);
		  }
		  
	  return $z;
	}

function getmatrix(&$b)
	  {
	  $n = sizeof($b);
	  for ($i = 0; $i < $n; $i++) 
		  {      
		    $x = $b[$i][0];
		    $A[$n - 1][$i] = 1;
		    for($j = $n - 2; $j >= 0; $j--) 	    
		      $A[$j][$i] = $this->field_mult($A[$j + 1][$i], $x);
		    
		    $x=$this->field_mult($x, $A[0][$i]);
		    $b[$i][1] = gmp_xor($b[$i][1], $x);
		  }
	  return $A;
	  }
	            
function restore_secret($shares) 
	{  
	  $coefs = $this->getmatrix($shares);
	  $this->degree = strlen(ltrim(bin2hex(gmp_export($shares[0][1])),"0"))*4;
	  $this->poly   = $this->field_init();
	  $n = sizeof($shares);
	  for($i = 0; $i < $n; $i++) 
		  {
		    if (!($coefs[$i][$i] > 0)) 
			    {
			      for($found = 0, $j = $i + 1; $j < $n; $j++)
				if (($coefs[$i][$j] > 0)) 
					{$found = 1;break;}
			      if (!$found) return -1;
			      for($k = $i; $k < $n; $k++) 
					$this->swap($coefs[$k][$i], $coefs[$k][$j]);
			      $this->swap($shares[$i][1], $shares[$j][1]);
			    }
		
		    for($j = $i + 1; $j < $n; $j++) 
			    {
			      if ($coefs[$i][$j] > 0) 
				      {
					for($k = $i + 1; $k < $n; $k++) 
						{
						  $h = $this->field_mult($coefs[$k][$i], $coefs[$i][$j]);
						  $coefs[$k][$j] = gmp_xor($this->field_mult($coefs[$k][$j], $coefs[$i][$i]),$h);
						}
				
					$h = $this->field_mult($shares[$i][1], $coefs[$i][$j]);
					$shares[$j][1] = gmp_xor($this->field_mult($shares[$j][1], $coefs[$i][$i]),$h);
				      }
			    }
		  }
	
	  $h = $this->field_invert($coefs[$n - 1][$n - 1]);  	  
	  return $this->teadecrypt($this->field_mult($shares[$n - 1][1], $h)); 
	}
}
/*
1-1c41ef496eccfbeba439714085df8437236298da8dd824
2-fbc74a03a50e14ab406c225afb5f45c40ae11976d2b665
3-fa1c3a9c6df8af0779c36de6c33f6e36e989d0e0b91309
4-468de7d6eb36674c9cf008c8e8fc8c566537ad6301eb9e
5-4756974923c0dce0a55f4774d09ca7a4865f64f56a4ee0
*/	
$shares=array(
"1-1c41ef496eccfbeba439714085df8437236298da8dd824",
"2-fbc74a03a50e14ab406c225afb5f45c40ae11976d2b665",
"3-fa1c3a9c6df8af0779c36de6c33f6e36e989d0e0b91309"
);

$ishares=array();
foreach ($shares as $share) 
	{
	$index=explode("-",$share);
	$ishares[]=[$index[0],gmp_init("0x".$index[1])];
	}

$x=new shamir_original;
 
echo $x->restore_secret($ishares);
?>
		    