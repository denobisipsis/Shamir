<?php
/*
#Implementation of Shamir Secret Sharing Scheme

http://web.mit.edu/6.857/OldStuff/Fall03/ref/Shamir-HowToShareASecret.pdf


SSS is based on the mathematical concept of polynomial interpolation which states that a polynomial of degree t-1 can be reconstructed from the knowledge of t or more points, known to be lying on the curve.
For instance, to reconstruct a curve of degree 1 (a straight line), we require at least 2 points that lie on the line 

More precisely, to establish a (t, n) secret sharing scheme, we can construct a polynomial of degree t-1 and pick n points on the curve as shares such that the polynomial will only be regenerated if t or more shares are pooled. The secret value (s) is concealed in the constant term of the polynomial (coefficient of 0-degree term or the curve’s y-intercept) which can only be obtained after the successful reconstruction of the curve.

From Shamir

2. A Simple (k, n) Threshold Scheme

Our scheme is based on polynomial' interpolation:
given k points in the 2-dimensional plane (x,, y,) .....
(xk, Yk). with distinct xi's , there is one and only one
polynomial q(x) of degree k - 1 such that q(x) =yi for all
i. 

Without loss of generality, we can assume that the data
D is (or can be made) a number. To divide it into pieces
D~, we pick a random k-1 degree polynomial
q(x)=ao+alx+ ... ak_ixk-~ in which ao=D , and
evaluate:

D~ = q(1) ..... D i = q(i) ..... D n = q(n).

Given any subset of k of these D~ values (together with
their identifying indices), we can find the coefficients of
q(x) by interpolation, and then evaluate D=q(O).

Knowledge of just k- 1 of these values, on the other
hand, does not suffice in order to calculate D.

To make this claim more precise, we use modular
arithmetic instead of real arithmetic. The set of integers
modulo a prime number p forms a field in which interpolation 
is possible. Given an integer valued data D, we
pick a prime p which is bigger than both D and n. The
coefficients a~ ..... ak_~ in q(x) are randomly chosen
from a uniform distribution over the integers in [0, p),
and the values D~ ..... Dn are computed modulo p.
Let us now assume that k-1 of these n pieces are
revealed to an opponent. For each candidate value D' in
[0, p) he can construct one and only one polynomial
q '(x) of degree k- 1 such that q '(0) =D' and q '(0 =D~
for the k- 1 given arguments. By construction, these p
possible polynomials are equally likely, and thus there is
abolutely nothing the opponent can deduce about the
real value of D. 

1- Generation of Shares 

This phase involves the setup of the system as well as the generation of the shares. 
 

Decide the values for the number of participants (n) and the threshold (t) to secure some secret value (s)
Construct a random polynomial, P(x), with degree t-1 by choosing random coefficients of the polynomial. Set the constant term in the polynomial (coefficient of zero degree term) to be equal to the secret value s
To generate the n shares, randomly pick n points lying on the polynomial P(x)
Distribute the picked coordinates in the previous step among the participants. These act as the shares in the system

2- Reconstruction of Secret 

For reconstruction of the secret, a minimum of t participants are required to pool their shares. 
 

Collect t or more shares
Use an interpolation algorithm to reconstruct the polynomial, P'(x), from the shares. Lagrange’s Interpolation is an example of such an algorithm
Determine the value of the reconstructed polynomial for x = 0, i.e. calculate P'(0). This value reveals the constant term of the polynomial which happens to be the original secret. Thus, the secret is reconstructed

# and base 36,62,85,93 numbers.
# Base 36 numbers use the symbols 0..9A..Z
# Base 62 numbers use the symbols 0..9A..Za..z
# Base 85 numbers use the symbols 0..9A..Za..z!#$%&()*+-;<=>?@^_`{|}~
# Base 93 numbers use the symbols 0..9A..Za..z!#$%&()*+-;<=>?@^_`{|}~"',./:[\]
*/

class Shamir
{

	public $printable;
	public $primos;
	public $BASE;

	function __construct()
	{
		/*
		set of printable characters to convert numbers
		*/

		$p = [
			48,49,50,51,52,53,54,55,56,57,97,98,99,100,101,102,103,104,105,106,107,108,109,110,
			111,112,113,114,115,116,117,118,119,120,121,122,65,66,67,68,69,70,71,72,73,74,75,76,77,78,
			79,80,81,82,83,84,85,86,87,88,89,90,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,58,59,60,
			61,62,63,64,91,92,93,94,95,96,123,124,125,126,32,9,10,13,11,12
		];  

		$this->BASE = 100;
			
		$printable = [];
					
		for ($k=0; $k < $this->BASE; $k++) {

			$printable[] = chr($p[$k]);
		}

		$this->printable = $printable;

		# Construimos una lista de primos

		$P128 = bcadd(bcpow(2,128,0) , 51);
		$P257 = bcadd(bcpow(2,256,0) , 297);
		$P321 = bcadd(bcpow(2,320,0) , 27);
		$P385 = bcadd(bcpow(2,384,0) , 231);    
		$primos = array_merge(array($P128,$P257,$P321,$P385), Shamir::primos_Mersenne());   
		sort($primos);

		$this->primos = $primos;
	}
	    
	static function primos_Mersenne()
	{
		// Calcula todos los primos de Mersenne menores de 500 dígitos 

		$mersenne_prime_exponents = [
			2, 3, 5, 7, 13, 17, 19, 31, 61, 89, 107, 127, 521, 607, 1279, 2203, 2281, 3217, 4253
		];

		$primos = [];

		foreach ($mersenne_prime_exponents as $exp) {

			$primos[] = bcsub(bcpow(2,$exp),1);
		}

		return $primos;
	}

	function primo_suficiente($shares)
	{
		/*
		encuentra un primo mayor que todos los shares

		The set of integers
		modulo a prime number p forms a field in which interpolation 
		is possible
		*/

		foreach ($this->primos as $primo) {

			$greater = 0;
					
			foreach ($shares as $i) {

				if ($i[1] > $primo) {

					$greater = 1;
					break;
				}
							
				if (!$greater) {

					return $primo;
				}
			}

			return;
		}
	}

	function string_to_int($s) 
	{

		$output = 0;

		foreach (str_split($s) as $char) {

			$output = bcadd(bcmul($output, $this->BASE) , array_search($char, $this->printable));
		}

		return $output;
	}
	         
	function string_from_int($f) 
	{

		$chars = "";
		
		while ($f>0) {

			$chars .= $this->printable[bcmod($f , $this->BASE)];
			$f = bcdiv($f, $this->BASE, 0);
		}
				
		return strrev($chars);
	}

	function polinomio_random($degree, $intercept, $upper_bound)
	{
		if ($degree < 0) {

			die('Degree must be a non-negative number.');
		}
		
		$coefficients = [$intercept];
		$i = 0;

		while ($i++<$degree) {	 

				$coefficients[] = gmp_random_range(0, bcsub($upper_bound,1));
		}

		return $coefficients;
	}
    
	function puntos_del_polinomio($coefficients, $np, $primo)
	{
		/*
		Calcula los primeros n puntos del polinomio [ (1, f(1)), (2, f(2)), ... (n, f(n)) ]
		empieza con x=1 y calcula el valor de y
		calcula cada término & súmalo a y usando matemática modular 
		añade el punto a la lista 
		*/
		$points = [];
		foreach (range(1, $np+1) as $x) {

			$y = $coefficients[0];

			foreach (range(1, sizeof($coefficients)-1) as $i) {
					$exponentiation = bcmod(bcpow($x,$i) , $primo);
					$term = bcmod(bcmul($coefficients[$i] , $exponentiation) , $primo);
					$y = bcmod(bcadd($y , $term) , $primo);
			}

			$points[] = [$x, $y];
		}

		return $points;
	}
         
	function secret_int_to_points($secret_int, $pt, $np)
	{
		//  Captura los puntos de intersección de un polinomio aleatorio en y igual al secreto.

		if ($pt < 2) {

			die("Threshold must be >= 2.");
		}

		if ($pt > $np) {

			die("Threshold must be < the total number of points.");
		}

		$primo = Shamir::primo_suficiente([[$np,$secret_int]]);

		if (!$primo) {

			die("Error! Secret is too long for share calculation!");
		}

		$coefficients = Shamir::polinomio_random($pt-1, $secret_int, $primo);
		$points       = Shamir::puntos_del_polinomio($coefficients, $np, $primo);
		
		return $points;
	}
	         
	function point_to_share_string($point)
	{			    
		[$x, $y] = $point;
		$x_string = $this->string_from_int($x);
		$y_string = bin2hex(gmp_export($y));
		$share_string = "$x_string".'-'."$y_string";

		return $share_string;
	}
     
	function recover_secret($shares) 
	{
		$primo = $this->primo_suficiente($shares);

		for ($formula = $accum = 0; $formula < sizeof($shares); $formula++) {
			/* 
			Multiplicar los numeradores por encima y los denominadores or debajo para hacer la interpolación Lagrange
			*/
			
			for ($count = 0, $numerator = $denominator = 1; $count < sizeof($shares); $count++) {

				if ($formula == $count) continue; 

				$start = $shares[$formula][0];
				$next  = $shares[$count][0];
				$numerator = ($numerator * -$next) % $primo;
				$denominator = ($denominator * ($start - $next)) % $primo;
			}

			$value = $shares[$formula][1];
			$accum = bcmod(bcadd($primo , bcadd($accum , bcmul(bcmul($value , $numerator) , gmp_invert($denominator,$primo)))), $primo);
		}

		return $this->string_from_int($accum);
	}
     	    
	function create_shares($secret_string, $s, $n)
	{
	// Trocea el secreto convertido a integer en shares (pares de coordenadas x,y) 

		$secret_int = Shamir::string_to_int($secret_string);
		$points     = Shamir::secret_int_to_points($secret_int, $s, $n-1);
		$shares     = array();

		foreach ($points as $point) {

			$shares[] = Shamir::point_to_share_string($point);
		}

		return $shares;
	}
}


$x = new Shamir();

// genera 5 shares con un Threshold de 3

$shares = $x->create_shares("Supersecreto", 3, 5);

var_Dump($shares);

// Prepara los shares a recuperar

$oshare = [];

foreach ($shares as $share) {

	$share = explode("-",$share);
	$oshare[] = [(int)$share[0],gmp_init("0x".$share[1])];	
}
	
echo $x->recover_secret($oshare);exit;
