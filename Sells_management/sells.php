<?php
    // Inclure la configuration et gérer la session
    require_once '../includes/config.php';

    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Vérifier si l'utilisateur est connecté
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../Authentification_management/login.php');
        exit;
    }

    // Initialisation des variables pour les messages de succès/erreur
    $message = '';
    $message_type = '';

    // Gestion de la suppression
    if (isset($_POST['delete_sale'])) {
        $sale_id = filter_input(INPUT_POST, 'sale_id', FILTER_VALIDATE_INT);
        
        if ($sale_id) {
            try {
                $stmt = $pdo->prepare("DELETE FROM sales WHERE id = :sale_id");
                $stmt->execute([':sale_id' => $sale_id]);
                $message = 'Vente supprimée avec succès !';
                $message_type = 'success';
            } catch (PDOException $e) {
                $message = 'Erreur lors de la suppression : ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    }

    // Gestion de la mise à jour
    if (isset($_POST['update_sale'])) {
        $sale_id = filter_input(INPUT_POST, 'sale_id', FILTER_VALIDATE_INT);
        $product_id = filter_input(INPUT_POST, 'edit_product_id', FILTER_VALIDATE_INT);
        $qty = filter_input(INPUT_POST, 'edit_qty', FILTER_VALIDATE_INT);
        $price = filter_input(INPUT_POST, 'edit_price', FILTER_VALIDATE_FLOAT);
        $date = filter_input(INPUT_POST, 'edit_date', FILTER_SANITIZE_STRING);

        if ($sale_id && $product_id && $qty && $price && $date) {
            try {
                $stmt = $pdo->prepare("
                    UPDATE sales 
                    SET product_id = :product_id, 
                        qty = :qty, 
                        price = :price, 
                        date = :date 
                    WHERE id = :sale_id
                ");
                $stmt->execute([
                    ':product_id' => $product_id,
                    ':qty' => $qty,
                    ':price' => $price,
                    ':date' => $date,
                    ':sale_id' => $sale_id
                ]);
                $message = 'Vente mise à jour avec succès !';
                $message_type = 'success';
            } catch (PDOException $e) {
                $message = 'Erreur lors de la mise à jour : ' . $e->getMessage();
                $message_type = 'error';
            }
        } else {
            $message = 'Veuillez remplir correctement tous les champs.';
            $message_type = 'error';
        }
    }

    // Gestion de l'insertion des données
    if (isset($_POST['add_sale'])) {
        $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        $qty = filter_input(INPUT_POST, 'qty', FILTER_VALIDATE_INT);
        $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
        $date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_STRING);

        if ($product_id && $qty && $price && $date) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO sales (product_id, qty, price, date)
                    VALUES (:product_id, :qty, :price, :date)
                ");
                $stmt->execute([
                    ':product_id' => $product_id,
                    ':qty' => $qty,
                    ':price' => $price,
                    ':date' => $date,
                ]);
                $message = 'Vente ajoutée avec succès !';
                $message_type = 'success';
            } catch (PDOException $e) {
                $message = 'Erreur lors de l\'ajout : ' . $e->getMessage();
                $message_type = 'error';
            }
        } else {
            $message = 'Veuillez remplir correctement tous les champs.';
            $message_type = 'error';
        }
    }

    // Pagination variables
    $perPage = 7;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $perPage;

    // Récupérer les ventes avec pagination
    try {
        $stmt = $pdo->prepare("
        SELECT sales.id AS sale_id, sales.product_id, products.name AS product_name, sales.qty, sales.price, sales.date
        FROM sales
        JOIN products ON sales.product_id = products.id
        ORDER BY sales.date DESC
        LIMIT :offset, :perPage
    ");
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':perPage', $perPage, PDO::PARAM_INT);
    $stmt->execute();
    $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    

        $stmt = $pdo->query("SELECT COUNT(id) FROM sales");
        $totalSales = $stmt->fetchColumn();
        $totalPages = ceil($totalSales / $perPage);
    } catch (PDOException $e) {
        die("Erreur lors de la récupération des données : " . $e->getMessage());
    }

    ob_start();
    ?>

    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Informations sur les Ventes</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-gray-100">
    <div class="p-6">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">Détails des Ventes</h1>

        <!-- Message de succès/erreur -->
        <?php if (!empty($message)): ?>
            <div class="mb-4 p-4 rounded <?php echo $message_type === 'success' ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Bouton pour ajouter une vente -->
        <div class="mb-4">
            <button onclick="toggleAddModal()" class="bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 transition duration-200 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Ajouter une Vente
            </button>
        </div>

        <!-- Modal Ajout -->
        <div id="addSaleModal" class="hidden fixed inset-0 bg-gray-500 bg-opacity-75 flex justify-center items-center z-50">
            <div class="bg-white p-6 rounded-lg shadow-lg w-96">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-bold">Ajouter une Nouvelle Vente</h2>
                    <button onclick="toggleAddModal()" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form method="POST" class="space-y-4">
                <div>
    <label for="product_id" class="block text-sm font-medium text-gray-700">Produit</label>
    <select name="product_id" id="product_id" required
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
        <?php
            // Fetch all products to populate the dropdown
            $stmt = $pdo->query("SELECT id, name FROM products");
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($products as $product):
        ?>
            <option value="<?php echo htmlspecialchars($product['id']); ?>">
                <?php echo htmlspecialchars($product['name']); ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

                    <div>
                        <label for="qty" class="block text-sm font-medium text-gray-700">Quantité</label>
                        <input type="number" name="qty" id="qty" min="1" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-700">Prix</label>
                        <input type="number" name="price" id="price" step="0.01" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
                        <input type="date" name="date" id="date" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="flex justify-end space-x-4">
                        <button type="button" onclick="toggleAddModal()" 
                                class="bg-gray-300 text-black px-4 py-2 rounded-md hover:bg-gray-400 transition duration-200">
                            Annuler
                        </button>
                        <button type="submit" name="add_sale" 
                                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-200">
                            Ajouter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Modification -->
        <div id="editSaleModal" class="hidden fixed inset-0 bg-gray-500 bg-opacity-75 flex justify-center items-center z-50">
            <div class="bg-white p-6 rounded-lg shadow-lg w-96">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-bold">Modifier la Vente</h2>
                    <button onclick="toggleEditModal()" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form method="POST" id="editForm" class="space-y-4">
                    <input type="hidden" name="sale_id" id="edit_sale_id">
                    <div>
                    <label for="edit_product_id" class="block text-sm font-medium text-gray-700">Produit</label>
                    <select name="edit_product_id" id="edit_product_id" required
    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
    <?php
        // Fetch all products to populate the dropdown
        $stmt = $pdo->query("SELECT id, name FROM products");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($products as $product):
            // Check if it's the current product being edited
            $selected = isset($sale) && $sale['product_id'] == $product['id'] ? 'selected' : '';
    ?>
        <option value="<?php echo htmlspecialchars($product['id']); ?>" <?php echo $selected; ?>>
            <?php echo htmlspecialchars($product['name']); ?>
        </option>
    <?php endforeach; ?>
</select>

                </div>

                    <div>
                        <label for="edit_qty" class="block text-sm font-medium text-gray-700">Quantité</label>
                        <input type="number" name="edit_qty" id="edit_qty" min="1" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="edit_price" class="block text-sm font-medium text-gray-700">Prix</label>
                        <input type="number" name="edit_price" id="edit_price" step="0.01" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="edit_date" class="block text-sm font-medium text-gray-700">Date</label>
                        <input type="date" name="edit_date" id="edit_date" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="flex justify-end space-x-4">
                        <button type="button" onclick="toggleEditModal()" 
                                class="bg-gray-300 text-black px-4 py-2 rounded-md hover:bg-gray-400 transition duration-200">
                            Annuler
                        </button>
                        <button type="submit" name="update_sale" 
                                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-200">
                            Mettre à jour
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tableau des ventes -->
    <!-- Tableau des ventes -->
    <div class="overflow-x-auto rounded-lg shadow-lg">
        <table id="salesTable" class="min-w-full bg-white border-collapse border border-gray-200">
            <thead class="bg-gray-100 text-gray-600 text-sm uppercase">
                <tr>
                    <th class="py-3 px-4 text-left border-b border-gray-300">ID Vente</th>
                    <th class="py-3 px-4 text-left border-b border-gray-300">ID Produit</th>
                    <th class="py-3 px-4 text-left border-b border-gray-300">Quantité</th>
                    <th class="py-3 px-4 text-left border-b border-gray-300">Prix</th>
                    <th class="py-3 px-4 text-left border-b border-gray-300">Date</th>
                    <th class="py-3 px-4 border-b border-gray-300 text-center uppercase text-sm font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-700 text-sm">
    <?php if (count($sales) > 0): ?>
        <?php foreach ($sales as $sale): ?>
            <tr class="hover:bg-gray-50">
                <td class="py-3 px-4 border-b border-gray-300"><?php echo htmlspecialchars($sale['sale_id']); ?></td>
                <td class="py-3 px-4 border-b border-gray-300"><?php echo htmlspecialchars($sale['product_name']); ?></td>
                <td class="py-3 px-4 border-b border-gray-300"><?php echo htmlspecialchars($sale['qty']); ?></td>
                <td class="py-3 px-4 border-b border-gray-300"><?php echo htmlspecialchars($sale['price']); ?> €</td>
                <td class="py-3 px-4 border-b border-gray-300"><?php echo htmlspecialchars($sale['date']); ?></td>
                            <td class="py-4 px-5 border-b">
                                <div class="flex justify-center space-x-2">
                                    <button onclick="editSale(<?php echo htmlspecialchars(json_encode($sale)); ?>)" 
                                            class="text-blue-500 hover:underline">
                                            <i class="fas fa-edit"></i>
                                        Modifier
                                    </button>
                                    
                                    <button onclick="showDeleteModal(<?php echo $sale['sale_id']; ?>)" 
                                            class="text-red-500 hover:underline ml-4">
                                            <i class="fas fa-trash-alt"></i>
                                        Supprimer
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-6 text-gray-500">Aucune vente trouvée.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

        <!-- Pagination -->
        <div class="mt-6 flex justify-center space-x-2">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" 
                class="px-4 py-2 border border-gray-300 rounded-md <?php echo $i === $page ? 'bg-blue-600 text-white' : 'bg-white text-blue-600 hover:bg-blue-50'; ?> transition duration-200">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg w-96">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">Confirmer la suppression</h2>
                <button onclick="closeDeleteModal()" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <p class="text-gray-700 mb-4">Êtes-vous sûr de vouloir supprimer cette vente ? Cette action est irréversible.</p>
            <form method="POST" id="deleteForm">
                <input type="hidden" name="sale_id" id="delete_sale_id">
                <div class="flex justify-end space-x-4">
                    <button type="button" onclick="closeDeleteModal()" 
                            class="bg-gray-300 text-black px-4 py-2 rounded-md hover:bg-gray-400 transition duration-200">
                        Annuler
                    </button>
                    <button type="submit" name="delete_sale" 
                            class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition duration-200">
                        Supprimer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function toggleAddModal() {
        const modal = document.getElementById('addSaleModal');
        modal.classList.toggle('hidden');
    }

    function toggleEditModal() {
        const modal = document.getElementById('editSaleModal');
        modal.classList.toggle('hidden');
    }

    function editSale(sale) {
        document.getElementById('edit_sale_id').value = sale.sale_id;
        document.getElementById('edit_product_id').value = sale.product_id;
        document.getElementById('edit_qty').value = sale.qty;
        document.getElementById('edit_price').value = sale.price;
        document.getElementById('edit_date').value = sale.date;
        toggleEditModal();
    }

    function showDeleteModal(saleId) {
        document.getElementById('delete_sale_id').value = saleId;
        document.getElementById('deleteModal').classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }

    // Fermer les modals si on clique en dehors
    window.onclick = function(event) {
        const addModal = document.getElementById('addSaleModal');
        const editModal = document.getElementById('editSaleModal');
        const deleteModal = document.getElementById('deleteModal');
        
        if (event.target === addModal) {
            addModal.classList.add('hidden');
        }
        if (event.target === editModal) {
            editModal.classList.add('hidden');
        }
        if (event.target === deleteModal) {
            deleteModal.classList.add('hidden');
        }
    }

    // Fermer les modals avec la touche Echap
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const addModal = document.getElementById('addSaleModal');
            const editModal = document.getElementById('editSaleModal');
            const deleteModal = document.getElementById('deleteModal');
            
            if (!addModal.classList.contains('hidden')) {
                addModal.classList.add('hidden');
            }
            if (!editModal.classList.contains('hidden')) {
                editModal.classList.add('hidden');
            }
            if (!deleteModal.classList.contains('hidden')) {
                deleteModal.classList.add('hidden');
            }
        }
    });
    </script>

    </body>
    </html>

    <?php
    $content = ob_get_clean();
    $page_title = 'Informations sur les Ventes';
    require_once '../layouts/layout.php';
    ?>