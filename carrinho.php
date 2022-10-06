<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="shortcut icon" type="imagex/png" href="../img/logo/logo.png">
  <link rel="stylesheet" type="text/css" href="../cabecalho/pag_inicial.css" />
  <title>Iluminatta - Otica e Joalheria</title>
</head>
<body>
  <?php
   
  ?>
</body>
</html>


<?php
 session_start(); // abre a sessão
 // verifica se não existe a sessão responsável por guardar nossos valores
  if ( !isset($_SESSION['carrinho']) ) 
 {
   $_SESSION['carrinho'] = array(); 
 }
 
 // verifica a ação
 if ( isset($_GET['acao']) )
 {
   // se é adicionar produto	 
   if ($_GET['acao'] == 'add')
   {
	  $id = $_GET['id'];
	  echo 'inserido o útimo produto de código '.$id.'<br/>'; 
	  // se não existir o produto no carrinho
	  if (!isset($_SESSION['carrinho'][$id]) )
	  {
		$_SESSION['carrinho'][$id] = 1; // quantidade receberá 1 inicialmente  
	  }
	  else // se caso já existir adiciona +1 para o que já tem
	  {
		$_SESSION['carrinho'][$id] += 1;  
	  }
   }
   
   // REMOVER O PRODUTO DO ARRAY
   if ($_GET['acao'] == 'del')
   {
	  $id = $_GET['id'];
	  if (isset($_SESSION['carrinho'][$id]))
	  {
		/// limpa o produto no carrinho...   
		unset($_SESSION['carrinho'][$id]);  
	  }	  
		  
   }    
   
    //// ATUALIZAR O Carrinho
	if ($_GET['acao'] == 'up')
	{
		if ( is_array($_POST['prod']) )
		{
			  foreach($_POST['prod'] as $id => $qtd	)
			  {
				    $id = intval($id);
					$qtd = intval($qtd);
					if (!empty($qtd) || $qtd <> 0)
					{
						$_SESSION['carrinho'][$id] = $qtd;
					}
					else
					{
						unset($_SESSION['carrinho'][$id]); ///limpa o carrinho
					}
			  }
		}
	}
	

 }
 
 print_r($_SESSION['carrinho']);
 
?>
<body>
 <h1>Carrinho de Compras</h1>
 
  <form action="carrinho.php?acao=up" method="post">
      <a href="ex_arrays_carrinho.php">Continuar comprando...</a>  


  <?php
      // Usa função count para verificar se o carrinho está vazio
      if ( count($_SESSION['carrinho'])==0 )
	  {  
         echo 'Não há produto no carrinho!';
	  }
	  else
	  {
		// desenho a tabela   
		echo "<table border='1' width='50%'>
		        <tr>
			      <th width='244'>Produto</th>
			      <th width='279'>Qtd</th>
			      <th width='89'>Preço</th>
			      <th width='100'>Subtotal</th>
			      <th width='64'>Remover</th>
		        </tr>";  
			  
		include "conecta.php";  
		// percorre o array
        foreach ( $_SESSION['carrinho'] as $id => $qtd)
		{
          $sql = "SELECT COD_PRODUTO,NOME,PRECO,CHAVE_IMG_PROD, CAMINHO_IMG 
		          FROM   tb_produto, tb_varias_img 
				  WHERE  COD_PRODUTO = '$id'";
          $resultado = mysqli_query($conn,$sql) or die (mysqli_error());
		  $linha = mysqli_fetch_array($resultado);
		  
		  //0 id, 1 nome, 2 preco e 3 imagem
		  $nome = $linha[1];
		  $preco = $linha[2];
		  $subtotal = $linha[2] * $qtd;
		  
		  echo " <tr>
					<th width='244'>$nome</th>                           
					<th width='279'><input type='text' size='3' name='prod[$id]' value='$qtd'/></th>
					<th width='89'>$preco</th>
					<th width='100'>$subtotal</th>
					<th width='64'><a href='carrinho.php?acao=del&id=$id'>REMOVER</a></th>
				  </tr>";				 
        }		
		echo '</table>';  
	  }
	   
    ?>  
       <p><input type="submit" value="Atualizar"/></p>
     </form>
	 
 <?php
    if ( count($_SESSION['carrinho'])<>0 )
	{
		echo "<form action='' method='get'>
		        <p><input type='submit' name='finalizar' value='Finalizar Pedido'/></p>
			  </form>";
			  
		if (isset($_GET['finalizar']))
        {  // gera o pedido
           $sqlgeraped = 'INSERT INTO TB_PEDIDOS
                           (DATAHORA,TOTAL)
                          VALUES
                           (CURRENT_TIMESTAMP,0)';
		   mysqli_query($conn,$sqlgeraped) or die (mysqli_error());

		   // fiz uma consulta para pegar esse último pedido
           $x = 'SELECT MAX(CODIGO) as maiorcodigo
		         FROM TB_PEDIDOS';
           $queryconsulta = mysqli_query($conn,$x) or die (mysqli_error());	
           $linha =  mysqli_fetch_assoc($queryconsulta);	
           //echo 'disparou o dolar linha<br/>';
           $ultpedido = 0; // limpa		   
		   $ultpedido = $linha['maiorcodigo'];
		   echo '<b><i>ULTIMO PEDIDO:</i></b>' . $ultpedido;
		   /////printf ("%s <br/>", $linha['maiorcodigo']);
		   
		   // percorro o array
           foreach ( $_SESSION['carrinho'] as $id => $qtd)
		   {
			  ///somente para pegar o preço do produto
			  $sql = "SELECT id,nome,preco,imagem
					  FROM   TB_PRODUTOS
					  WHERE  ID = '$id'";
			  $resultado = mysqli_query($conn,$sql) or die (mysqli_error());
			  $registro = mysqli_fetch_array($resultado);
			  $valor = $registro[2]*$qtd;

			   $inspeditem = "INSERT INTO TB_PEDIDO_ITENS
                                (PRODUTO,QTD,VALOR,PEDIDO)
                              VALUES
                                ($id,$qtd,$valor,$ultpedido)";
			   mysqli_query($conn,$inspeditem) or die (mysqli_error());				
               
            }
		   
		   echo "<br/><br/>Pedido finalizado com sucesso
		        </form>";
			
           // agora que finalizou o pedido na linha acima, aparecerá um botão para sair			
		   echo "<form action='' method='get''>
		           <p><input type='submit' name='sair' value='Clique para sair...'/></p>
		         </form>";
				 

			if (!isset($_GET['sair']))	
            {
			  // remove all session variables
			  session_unset();

			  // destroy the session
			  session_destroy();
            }
           			
        }		   
	}
?> 
  
</body>