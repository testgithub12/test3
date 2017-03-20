<?php
class ProductsController extends Controller {
	/* 
	*	Add new product
	*/
	 
	public function addAction() {

		$loadView = $this->loadView('Products');

		
		if (isset($_POST['submit']))
		{
			$loadModel = $this->loadModel('Products');
			
			//form validation
			
			$requiredFields=array('partNumber', 'description', 'stockQTY', 'costPrice', 'sellingPrice', 'vatRate'); //required fields in form
			
			if (!$loadModel->checkEmptyFields($requiredFields)) $loadView->setMsg('Fill all required fields.');
			else
			{
				$allowedType = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "png" => "image/png"); // allowed types of image upload
				
				$uploadModel = $this->loadModel('Upload', $_FILES['image']);
				
				
				if (!$uploadModel->checkIsUploaded()) $loadView->setMsg('Something wrong with uploading file.');
				elseif (!$uploadModel->checkType($allowedType)) $loadView->setMsg('File type is wrong. Only JPG, JPEG, PNG');
				elseif (!$uploadModel->uploadFile()) $loadView->setMsg('File cant be uploaded. Contact Administrator.');
				elseif (!$loadModel->isNumeric($_POST['stockQTY']) || 
						!$loadModel->isNumeric($_POST['costPrice']) ||
						!$loadModel->isNumeric($_POST['sellingPrice']) ||
						!$loadModel->isNumeric($_POST['vatRate'])) $loadView->setMsg('Prices, stock qty should be numbers!');
				else
				{		
						//adding new product
						
						$image = $uploadModel->newFileName;
						
						$values = array(
							'part_number' => $_POST['partNumber'],
							'description' => htmlspecialchars($_POST['description']),
							'image' => $image,
							'stock_quantity' => $_POST['stockQTY'],
							'cost_price' => $_POST['costPrice'],
							'selling_price' => $_POST['sellingPrice'],
							'vat_rate' => $_POST['vatRate']
						);
						
						$loadModel->insertProduct($values);
						
						//flash message, show message after redirect
						$loadView->setFlashMsg('Product added successfully!', 'success');
						$this->redirect('index.php?controller=Products&action=list');
				}	
			}
		}
		
		$loadView->addView(); //show template
	}
	
	/*
	*	Edit product
	*/
	
	public function editAction() {
		$loadView = $this->loadView('Products');
		
		$loadModel = $this->loadModel('Products');
		
		//get product id and details
		
		$getProductId = $loadModel->getRoute('id');
		$getProduct = $loadModel->getProduct($getProductId);
		
		//check if product exists
		
		if (empty($getProduct['product_id'])) { 
			$loadView->setMsg("Wrong ID, product doesn't exist.");
			$loadView->error = 1;
		}
		else
		{
			$loadView->setVar('product', $getProduct);
			
			if (isset($_POST['submit']))
			{
				//form validation
				
				$requiredFields=array('partNumber', 'description', 'stockQTY', 'costPrice', 'sellingPrice', 'vatRate'); //required fields in form
				
				if (!$loadModel->checkEmptyFields($requiredFields)) $loadView->setMsg('Fill all required fields.');
				else
				{
					
					if (!$loadModel->isNumeric($_POST['stockQTY']) || 
						!$loadModel->isNumeric($_POST['costPrice']) ||
						!$loadModel->isNumeric($_POST['sellingPrice']) ||
						!$loadModel->isNumeric($_POST['vatRate'])) $loadView->setMsg('Prices, stock qty should be numbers!');
					else
					{
							//check if image was uploaded
							if (is_uploaded_file($_FILES['image']['tmp_name'])) {
								$allowedType = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "png" => "image/png");
				
								$uploadModel = $this->loadModel('Upload', $_FILES['image']);
								
								if (!$uploadModel->checkType($allowedType)) { $loadView->setMsg('File type is wrong. Only JPG, JPEG, PNG'); $upload_error=1; }
								elseif (!$uploadModel->uploadFile()) { $loadView->setMsg('File cant be uploaded. Contact Administrator.');  $upload_error=1; }
								else
								{
									//updating image
									$image = $uploadModel->newFileName;
									
									$values=array(
										'product_id' => $getProduct['product_id'],
										'image' => $image
									);
									
									$loadModel->deleteImage($getProduct['image']);
									
									$loadModel->updateImage($values);
								}
							}
							
							//updating product details
							
							$values = array(
								'product_id' => $getProduct['product_id'],
								'part_number' => $_POST['partNumber'],
								'description' => htmlspecialchars($_POST['description']),
								'stock_quantity' => $_POST['stockQTY'],
								'cost_price' => $_POST['costPrice'],
								'selling_price' => $_POST['sellingPrice'],
								'vat_rate' => $_POST['vatRate']
							);
							
							$loadModel->editProduct($values);
							
							if (empty($upload_error)) { // redirect if everything is ok
								$loadView->setFlashMsg('Product updated successfully!', 'success');
								$this->redirect('index.php?controller=Products&action=list');
							}
							else 
							{
								//update product details to form, if upload error only
								$getProduct = $loadModel->getProduct($getProductId);
								$loadView->setVar('product', $getProduct);
							}
					}
				}
			}
		}
		
		$loadView->editView(); //show template
	}
	
	/*
	*	Delete product
	*/
	
	public function deleteAction() {
		$loadView = $this->loadView('Products');
		
		$loadModel = $this->loadModel('Products');
		
		
		//get product id and details
		
		$getProductId = $loadModel->getRoute('id');
		$getProduct = $loadModel->getProduct($getProductId);
		
		//check if product exist
		
		if (empty($getProduct['product_id'])) { 
			$loadView->setFlashMsg("Product doesn't exist. Wrong ID!");
		}
		else
		{
			//deleting image and product
			$loadModel->deleteImage($getProduct['image']);
			$loadModel->deleteProduct($getProduct['product_id']);
			
			//flash message, show message after redirect
			$loadView->setFlashMsg("Deleted successfully.", 'success');
		}
		
		//redirect to product list
		$this->redirect('index.php?controller=Products&action=list');
	}
	
	/*
	*	Product list
	*/
	
	public function listAction() {
		$loadView = $this->loadView('Products');
		
		$loadModel = $this->loadModel('Products');
		
		//get all products
		$getAllProducts = $loadModel->getAllProducts();
		$loadView->setVar('products', $getAllProducts);
		
		
		$loadView->listView(); //show template
	}
	
}
