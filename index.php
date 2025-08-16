<?php 

if (!file_exists('data')) {
    mkdir('data');
}

$deals = file_exists('data/deals.json') ? json_decode(file_get_contents('data/deals.json'), true) : [];
$contacts = file_exists('data/contacts.json') ? json_decode(file_get_contents('data/contacts.json'), true) : [];

$selectedMenu = $_GET['menu'] ?? 'deals';
$selectedItemId = $_GET['id'] ?? array_key_first($deals);

function saveData($type, $data) {
    file_put_contents("data/$type.json", json_encode($data));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post = $_POST;
    $action = $_GET['action'] ?? '';
    $response = ['success' => false];

    if ($action === 'add_deal') {
        $id = uniqid();
        if (!empty($post['name'])) {
            $deals[$id] = [
                'id' => $id,
                'name' => $post['name'],
                'summ' => $post['summ'] ?? '',
                'contacts' => $post['contacts'] ?? []
            ];
            saveData('deals', $deals);
            $response = ['success' => true, 'id' => $id];
        } else {
            $response['error'] = 'Название сделки обязательно';
        }
    }

    elseif ($action === 'edit_deal') {
    if (isset($deals[$post['id']])) {
        if (isset($post['name'])) {
            if (empty($post['name'])) {
                echo json_encode(['success' => false, 'error' => 'Название сделки обязательно']);
                exit;
            }
            $deals[$post['id']]['name'] = $post['name'];
        }
        if (isset($post['summ'])) {
            $deals[$post['id']]['summ'] = $post['summ'];
        }
        saveData('deals', $deals);
        $response = ['success' => true];
    } else {
        $response['error'] = 'Сделка не найдена';
    }
}

    elseif ($action === 'delete_deal' && isset($deals[$post['id']])) {
        unset($deals[$post['id']]);
        saveData('deals', $deals);
        $response = ['success' => true];
    }

    elseif ($action === 'add_contact') {
        $id = uniqid();
        if (!empty($post['name'])) {
            $contacts[$id] = [
                'id' => $id,
                'name' => $post['name'],
                'surname' => $post['surname'] ?? '',
                'deals' => $post['deals'] ?? [],
            ];
            saveData('contacts', $contacts);
            $response = ['success' => true, 'id' => $id];
        } else {
            $response['error'] = 'Имя контакта обязательно';
        }
    }
    
    elseif ($action === 'edit_contact') {
    if (isset($contacts[$post['id']])) {
        if (isset($post['name'])) {
            if (empty($post['name'])) {
                echo json_encode(['success' => false, 'error' => 'Имя контакта обязательно']);
                exit;
            }
            $contacts[$post['id']]['name'] = $post['name'];
        }
        if (isset($post['surname'])) {
            $contacts[$post['id']]['surname'] = $post['surname'];
        }
        saveData('contacts', $contacts);
        $response = ['success' => true];
    } else {
        $response['error'] = 'Контакт не найден';
    }
}

    elseif ($action === 'delete_contact' && isset($contacts[$post['id']])) {
        unset($contacts[$post['id']]);
        saveData('contacts', $contacts);
        $response = ['success' => true];
    }

    elseif ($action === 'update_relations') {
        if ($post['type'] === 'deal' && isset($deals[$post['id']])) {
            $deals[$post['id']]['contacts'] = $post['contacts'] ?? [];
            saveData('deals', $deals);
            $response = ['success' => true];
        } elseif ($post['type'] === 'contact' && isset($contacts[$post['id']])) {
            $contacts[$post['id']]['deals'] = $post['deals'] ?? [];
            saveData('contacts', $contacts);
            $response = ['success' => true];
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
} 

$currentItem = null;
if ($selectedItemId) {
    $currentItem = $selectedMenu === 'deals' 
        ? ($deals[$selectedItemId] ?? null)
        : ($contacts[$selectedItemId] ?? null);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/reset.css">
    <link rel="stylesheet" href="css/general.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>ТЗ</title>
</head> 
<body>
    <div class="container">
        <div class="col menu-col">
            <h3>Меню</h3>
            <div class="clickable col-div <?= $selectedMenu === 'deals' ? 'selected' : '' ?>"
            onclick="location.href='?menu=deals<?= $selectedMenu === 'deals' ? '' : '&id='.array_key_first($deals) ?>'">
                Сделки
            </div>
            <div class="clickable col-div <?= $selectedMenu === 'contacts' ? 'selected' : '' ?>"
            onclick="location.href='?menu=contacts<?= $selectedMenu === 'contacts' ? '' : '&id='.array_key_first($contacts) ?>'">
                Контакты
            </div>
        </div>

        <div class="col list-col">
            <h3>Список</h3>
            <?php if ($selectedMenu === 'deals'): ?>
                <div class="clickable col-div" onclick="showAddForm('deal')" style="background: #e9f7ef;">
                    + Добавить сделку
                </div>
                <?php if (empty($deals)): ?>
                    <p class="empty-message">Нет сделок</p>
                <?php else: ?>
                    <?php foreach ($deals as $deal): ?>
                        <div class="clickable col-div <?= $selectedItemId === $deal['id'] ? 'selected' : '' ?>"
                            onclick="location.href='?menu=deals&id=<?=$deal['id']?>'">
                            <?= htmlspecialchars($deal['name']) ?>
                        </div>
                    <?php endforeach;?>
                <?php endif;?>
            <?php else: ?>
                <div class="clickable col-div" onclick="showAddForm('contact')" style="background: #e9f7ef;">
                    + Добавить контакт
                </div>
                <?php if (empty($contacts)): ?>
                    <p class="empty-message">Нет контактов</p>
                <?php else: ?>    
                    <?php foreach ($contacts as $contact): ?>
                        <div class="clickable col-div <?= $selectedItemId === $contact['id'] ? 'selected' : '' ?>" 
                            onclick="location.href='?menu=contacts&id=<?=$contact['id']?>'">
                            <?= htmlspecialchars($contact['name']) ?>
                        </div>
                    <?php endforeach;?>
                <?php endif;?>
            <?php endif;?>
        </div>

        <div class="col content-col">
            <h3>Содержимое</h3>
            <?php if ($currentItem): ?>
                <?php if ($selectedMenu === 'deals'): ?>
                    <table>
                        <tr>
                            <td>id сделки</td>
                            <td><?= $currentItem['id'] ?></td>
                        </tr>
                        <tr>
                            <td>Наименование</td>
                            <td>
                                <span class="editable" data-field="name" data-id="<?= $currentItem['id'] ?>">
                                    <?= htmlspecialchars($currentItem['name']) ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td>Сумма</td>
                            <td>
                                <span class="editable" data-field="summ" data-id="<?= $currentItem['id'] ?>">
                                    <?= htmlspecialchars($currentItem['summ']) ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td>Контакты</td>
                            <td>
                                <select multiple class="relations-select" data-type="deal" data-id="<?= $currentItem['id'] ?>">
                                    <?php foreach ($contacts as $contact): ?>
                                        <option value="<?= $contact['id'] ?>" <?= in_array($contact['id'], $currentItem['contacts'] ?? []) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($contact['name'] . ' ' . $contact['surname']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                    </table>
                <?php else: ?> 
                    <table>
                        <tr>
                            <td>id контакта</td>
                            <td><?= $currentItem['id'] ?></td>
                        </tr>
                        <tr>
                            <td>Имя</td>
                            <td>
                                <span class="editable" data-field="name" data-id="<?= $currentItem['id'] ?>">
                                    <?= htmlspecialchars($currentItem['name']) ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td>Фамилия</td>
                            <td>
                                <span class="editable" data-field="surname" data-id="<?= $currentItem['id'] ?>">
                                    <?= htmlspecialchars($currentItem['surname']) ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td>Сделки</td>
                            <td>
                                <select multiple class="relations-select" data-type="contact" data-id="<?= $currentItem['id'] ?>">
                                    <?php foreach ($deals as $deal): ?>
                                        <option value="<?= $deal['id'] ?>" <?= in_array($deal['id'], $currentItem['deals'] ?? []) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($deal['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                    </table>
                <?php endif; ?>
                
                <div class="action-buttons">
                    <button class="btn btn-danger" onclick="deleteItem('<?= $selectedMenu === 'deals' ? 'deal' : 'contact' ?>', '<?= $currentItem['id'] ?>')">
                        Удалить
                    </button>
                </div>
            <?php else: ?>
                <p>Выберите элемент из списка или создайте новый</p>
            <?php endif; ?>
            
            <div id="add-deal-form" style="display: none; margin-top: 20px;">
                <h4>Добавить сделку</h4>
                <div>
                    <label>Название*:</label>
                    <input type="text" id="deal-name" class="edit-input" required>
                </div>
                <div>
                    <label>Сумма:</label>
                    <input type="text" id="deal-summ" class="edit-input">
                </div>
                <div class="action-buttons">
                    <button class="btn btn-primary" onclick="addItem('deal')">Сохранить</button>
                    <button class="btn" onclick="$('#add-deal-form').hide()">Отмена</button>
                </div>
            </div>
            
            <div id="add-contact-form" style="display: none; margin-top: 20px;">
                <h4>Добавить контакт</h4>
                <div>
                    <label>Имя*:</label>
                    <input type="text" id="contact-name" class="edit-input" required>
                </div>
                <div>
                    <label>Фамилия:</label>
                    <input type="text" id="contact-surname" class="edit-input">
                </div>
                <div class="action-buttons">
                    <button class="btn btn-primary" onclick="addItem('contact')">Сохранить</button>
                    <button class="btn" onclick="$('#add-contact-form').hide()">Отмена</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        $('.editable').click(function() {
            const field = $(this).data('field');
            const id = $(this).data('id');
            const value = $(this).text().trim();
            
            $(this).html(`<input type="text" class="edit-input" value="${value}" data-field="${field}" data-id="${id}">`);
            $(this).find('input').focus();
            
            $(this).find('input').keypress(function(e) {
                if (e.which === 13) {
                    saveField($(this));
                }
            });
            
            $(this).find('input').blur(function() {
                saveField($(this));
            });
        });
        
        $('.relations-select').change(function() {
            const type = $(this).data('type');
            const id = $(this).data('id');
            const selected = $(this).val() || [];
            
            $.post('?action=update_relations', {
                type: type,
                id: id,
                [type === 'deal' ? 'contacts' : 'deals']: selected
            }, function(response) {
                if (!response.success) {
                    alert('Ошибка при сохранении связей');
                }
            }, 'json');
        });
    });
    
    function saveField(input) {
        const field = input.data('field');
        const id = input.data('id');
        const value = input.val().trim();
        const type = window.location.href.includes('menu=deals') ? 'deal' : 'contact';
        
        const data = { id: id };
        data[field] = value;
        
        if (field === 'name') {
            if (value === '') {
                alert('Это поле обязательно для заполнения');
                input.focus();
                return;
            }
        }
        
        $.post(`?action=edit_${type}`, data, function(response) {
            if (response.success) {
                input.parent().text(value);
            } else {
                alert(response.error || 'Ошибка при сохранении');

                input.parent().text(input.parent().data('prev-value') || '');
            }
        }, 'json').fail(function() {
            alert('Ошибка соединения');
            input.parent().text(input.parent().data('prev-value') || '');
        });
    }
    
    function showAddForm(type) {
        $('#add-deal-form, #add-contact-form').hide();
        $(`#add-${type}-form`).show();
        $(`#${type}-name`).focus();
    }
    
    function addItem(type) {
        const nameField = $(`#${type}-name`);
        const name = nameField.val().trim();
        
        if (name === '') {
            alert('Поле имени обязательно для заполнения');
            nameField.focus();
            return;
        }
        
        const data = {
            name: name
        };
        
        if (type === 'deal') {
            data.summ = $('#deal-summ').val().trim();
        } else {
            data.surname = $('#contact-surname').val().trim();
        }
        
        $.post(`?action=add_${type}`, data, function(response) {
            if (response.success) {
                location.href = `?menu=${type}s&id=${response.id}`;
            } else {
                alert(response.error || 'Ошибка при добавлении');
            }
        }, 'json');
    }
    
    function deleteItem(type, id) {
        if (confirm(`Вы уверены, что хотите удалить этот ${type === 'deal' ? 'сделку' : 'контакт'}?`)) {
            $.post(`?action=delete_${type}`, {id: id}, function(response) {
                if (response.success) {
                    location.href = `?menu=${type}s`;
                } else {
                    alert('Ошибка при удалении');
                }
            }, 'json');
        }
    }
</script>
</body>
</html>