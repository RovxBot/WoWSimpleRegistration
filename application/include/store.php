<?php
/**
 * Grim Token player portal and SOAP-backed service checkout.
 */

class store_user_exception extends RuntimeException
{
}

class store
{
    public const ACTION_LOGIN = 'portal_login';
    public const ACTION_LOGOUT = 'portal_logout';
    public const ACTION_CHECKOUT = 'store_checkout';

    private const ORDER_TABLE = 'store_service_orders';
    private const SESSION_ACCOUNT_ID = 'portal_account_id';
    private const SESSION_USERNAME = 'portal_username';
    private const SESSION_EMAIL = 'portal_email';

    private static $catalog = null;
    private static $orderTableReady = false;

    public static function post_handler()
    {
        if (!self::is_enabled()) {
            return false;
        }

        self::portal_logout();
        self::portal_login();
        self::checkout();
        return true;
    }

    public static function is_enabled()
    {
        return !empty(get_config('store_enabled'));
    }

    public static function is_portal_action($action)
    {
        return in_array((string) $action, self::get_portal_actions(), true);
    }

    public static function get_portal_actions()
    {
        return array(
            self::ACTION_LOGIN,
            self::ACTION_LOGOUT,
            self::ACTION_CHECKOUT,
        );
    }

    public static function portal_login()
    {
        if (empty($_POST['submit']) || $_POST['submit'] !== self::ACTION_LOGIN) {
            return false;
        }

        if (get_config('battlenet_support')) {
            if (empty($_POST['email']) || empty($_POST['password'])) {
                error_msg(lang_or('account_is_not_valid', 'Account is not valid.'));
                return false;
            }

            $account = user::authenticate_battlenet_portal($_POST['email'], $_POST['password']);
        } else {
            if (empty($_POST['username']) || empty($_POST['password'])) {
                error_msg(lang_or('account_is_not_valid', 'Account is not valid.'));
                return false;
            }

            $account = user::authenticate_normal_portal($_POST['username'], $_POST['password']);
        }

        if (empty($account['id'])) {
            error_msg(lang_or('portal_login_failed', 'Login failed. Check your account details and try again.'));
            return false;
        }

        session_regenerate_id(true);
        $_SESSION[self::SESSION_ACCOUNT_ID] = (int) $account['id'];
        $_SESSION[self::SESSION_USERNAME] = (string) $account['username'];
        $_SESSION[self::SESSION_EMAIL] = (string) $account['email'];
        success_msg(lang_or('portal_login_success', 'Login successful. Welcome back.'));
        return true;
    }

    public static function portal_logout()
    {
        if (empty($_POST['submit']) || $_POST['submit'] !== self::ACTION_LOGOUT) {
            return false;
        }

        self::clear_portal_session();
        session_regenerate_id(true);
        success_msg(lang_or('portal_logout_success', 'You have been signed out.'));
        return true;
    }

    public static function is_portal_logged_in()
    {
        return !empty($_SESSION[self::SESSION_ACCOUNT_ID]);
    }

    public static function get_portal_account()
    {
        if (!self::is_portal_logged_in()) {
            return false;
        }

        $account = user::get_user_by_id((int) $_SESSION[self::SESSION_ACCOUNT_ID]);
        if (empty($account['id'])) {
            self::clear_portal_session();
            return false;
        }

        return $account;
    }

    public static function get_account_token_summary($account = null)
    {
        $accountId = self::extract_account_id($account);
        if ($accountId < 1) {
            return array(
                'total' => 0,
                'has_online_character' => false,
                'characters' => array(),
            );
        }

        $characters = self::get_account_characters($accountId);
        $total = 0;
        $hasOnlineCharacter = false;

        foreach ($characters as $character) {
            $total += (int) $character['grim_tokens'];
            if (!empty($character['online'])) {
                $hasOnlineCharacter = true;
            }
        }

        return array(
            'total' => $total,
            'has_online_character' => $hasOnlineCharacter,
            'characters' => $characters,
        );
    }

    public static function get_account_characters($account)
    {
        $accountId = self::extract_account_id($account);
        if ($accountId < 1) {
            return array();
        }

        $connection = self::get_chars_connection();
        if ($connection === false) {
            return array();
        }

        $statement = $connection->executeQuery(
            "SELECT
                c.guid,
                c.name,
                c.race,
                c.class,
                c.gender,
                c.level,
                c.online,
                COALESCE(SUM(ii.`count`), 0) AS grim_tokens
            FROM characters c
            LEFT JOIN character_inventory ci
                ON ci.guid = c.guid
            LEFT JOIN item_instance ii
                ON ii.guid = ci.item
                AND ii.itemEntry = :token_item_id
            WHERE c.account = :account_id
            GROUP BY c.guid, c.name, c.race, c.class, c.gender, c.level, c.online
            ORDER BY c.online DESC, c.name ASC",
            array(
                'account_id' => $accountId,
                'token_item_id' => self::get_token_item_id(),
            )
        );

        return $statement->fetchAllAssociative();
    }

    public static function get_catalog()
    {
        if (self::$catalog !== null) {
            return self::$catalog;
        }

        self::$catalog = array();
        $catalogFile = get_config('store_catalog_file');
        if (!is_string($catalogFile) || $catalogFile === '' || !is_file($catalogFile)) {
            return self::$catalog;
        }

        $services = include $catalogFile;
        if (!is_array($services)) {
            return self::$catalog;
        }

        foreach ($services as $serviceKey => $service) {
            if (!is_array($service)) {
                continue;
            }

            $serviceId = '';
            if (!empty($service['id']) && is_string($service['id'])) {
                $serviceId = strtolower(trim($service['id']));
            } elseif (is_string($serviceKey)) {
                $serviceId = strtolower(trim($serviceKey));
            }

            if ($serviceId === '' || !preg_match('/^[a-z0-9_-]+$/', $serviceId)) {
                continue;
            }

            $scope = strtolower((string) ($service['scope'] ?? ''));
            if (!in_array($scope, array('account', 'character'), true)) {
                continue;
            }

            $title = trim((string) ($service['title'] ?? ''));
            $soapCommand = trim((string) ($service['soap_command'] ?? ''));
            $price = (int) ($service['price'] ?? 0);
            if ($title === '' || $soapCommand === '' || $price < 1) {
                continue;
            }

            self::$catalog[$serviceId] = array(
                'id' => $serviceId,
                'title' => $title,
                'description' => trim((string) ($service['description'] ?? '')),
                'price' => $price,
                'scope' => $scope,
                'soap_command' => $soapCommand,
            );
        }

        return self::$catalog;
    }

    public static function get_recent_orders($account = null, $limit = 10)
    {
        $accountId = self::extract_account_id($account);
        if ($accountId < 1) {
            return array();
        }

        self::ensure_order_table();

        $statement = database::$auth->executeQuery(
            'SELECT id, service_title, service_scope, target_character_name, token_cost, status, created_at, fulfilled_at, refunded_at
            FROM ' . self::get_auth_order_table_reference(false) . '
            WHERE account_id = :account_id
            ORDER BY id DESC
            LIMIT ' . max(1, (int) $limit),
            array('account_id' => $accountId)
        );

        return $statement->fetchAllAssociative();
    }

    public static function get_order_status_label($status)
    {
        switch ((string) $status) {
            case 'fulfilled':
                return lang_or('store_status_fulfilled', 'Fulfilled');
            case 'failed_refunded':
                return lang_or('store_status_failed_refunded', 'Failed and Refunded');
            case 'unknown_needs_review':
                return lang_or('store_status_needs_review', 'Needs Review');
            case 'processing':
            default:
                return lang_or('store_status_processing', 'Processing');
        }
    }

    public static function get_order_status_badge_class($status)
    {
        switch ((string) $status) {
            case 'fulfilled':
                return 'is-success';
            case 'failed_refunded':
                return 'is-refunded';
            case 'unknown_needs_review':
                return 'is-review';
            case 'processing':
            default:
                return 'is-processing';
        }
    }

    public static function checkout()
    {
        if (empty($_POST['submit']) || $_POST['submit'] !== self::ACTION_CHECKOUT) {
            return false;
        }

        if (!self::is_portal_logged_in()) {
            error_msg(lang_or('store_login_required', 'Log in with your player account to view your store balance and catalog.'));
            return false;
        }

        $account = self::get_portal_account();
        if (empty($account['id'])) {
            error_msg(lang_or('portal_login_failed', 'Login failed. Check your account details and try again.'));
            return false;
        }

        $lockName = self::get_account_lock_name((int) $account['id']);
        if (!self::acquire_account_lock($lockName)) {
            error_msg(lang_or('store_busy', 'Another store checkout is already running for this account. Please try again in a moment.'));
            return false;
        }

        $purchaseContext = null;
        try {
            $purchaseContext = self::prepare_checkout($account);
            $soapResult = RemoteCommandWithSOAPDetailed($purchaseContext['soap_command']);

            if ($soapResult['status'] === 'success') {
                self::update_order_status(
                    $purchaseContext['order_id'],
                    'fulfilled',
                    self::summarize_soap_result($soapResult, lang_or('store_checkout_success', 'Service completed successfully.')),
                    'fulfilled_at'
                );
                success_msg(lang_or('store_checkout_success', 'Service completed successfully.'));
                return true;
            }

            if ($soapResult['status'] === 'failed') {
                self::refund_purchase($purchaseContext);
                self::update_order_status(
                    $purchaseContext['order_id'],
                    'failed_refunded',
                    self::summarize_soap_result($soapResult, lang_or('store_checkout_failed_refunded', 'The service failed and your Grim Tokens were refunded.')),
                    'refunded_at'
                );
                error_msg(lang_or('store_checkout_failed_refunded', 'The service failed and your Grim Tokens were refunded.'));
                return false;
            }

            self::update_order_status(
                $purchaseContext['order_id'],
                'unknown_needs_review',
                self::summarize_soap_result($soapResult, lang_or('store_checkout_needs_review', 'The service result could not be confirmed automatically. No automatic refund was issued.')),
                null
            );
            error_msg(lang_or('store_checkout_needs_review', 'The service result could not be confirmed automatically. No automatic refund was issued.'));
            return false;
        } catch (store_user_exception $exception) {
            error_msg($exception->getMessage());
            return false;
        } catch (Throwable $exception) {
            if (!empty($purchaseContext['order_id'])) {
                self::update_order_status(
                    $purchaseContext['order_id'],
                    'unknown_needs_review',
                    lang_or('store_checkout_internal_error', 'The store encountered an unexpected error and needs review.'),
                    null
                );
            }

            error_msg(lang_or('store_checkout_internal_error', 'The store encountered an unexpected error and needs review.'));
            return false;
        } finally {
            self::release_account_lock($lockName);
        }
    }

    private static function prepare_checkout($account)
    {
        self::ensure_order_table();

        $serviceId = strtolower(trim((string) ($_POST['service_id'] ?? '')));
        $catalog = self::get_catalog();
        if ($serviceId === '' || empty($catalog[$serviceId])) {
            throw new store_user_exception(lang_or('store_service_not_found', 'That service is not available.'));
        }

        $service = $catalog[$serviceId];
        $connection = self::get_chars_connection();
        if ($connection === false) {
            throw new store_user_exception(lang_or('store_checkout_internal_error', 'The store encountered an unexpected error and needs review.'));
        }

        $accountId = (int) $account['id'];
        $targetCharacterGuid = !empty($_POST['target_character_guid']) ? (int) $_POST['target_character_guid'] : 0;

        $connection->beginTransaction();

        try {
            $lockedCharacters = $connection->executeQuery(
                'SELECT guid, name, online FROM characters WHERE account = :account_id ORDER BY guid ASC FOR UPDATE',
                array('account_id' => $accountId)
            )->fetchAllAssociative();

            if (empty($lockedCharacters)) {
                throw new store_user_exception(lang_or('store_no_characters', 'No characters were found on this account.'));
            }

            foreach ($lockedCharacters as $character) {
                if (!empty($character['online'])) {
                    throw new store_user_exception(lang_or('store_characters_must_be_offline', 'All characters on this account must be offline before using the Grim Token shop.'));
                }
            }

            $targetCharacter = self::resolve_target_character($service, $lockedCharacters, $targetCharacterGuid);
            $tokenRows = $connection->executeQuery(
                "SELECT
                    c.guid AS character_guid,
                    c.name AS character_name,
                    ci.bag,
                    ci.slot,
                    ci.item AS item_guid,
                    ii.itemEntry,
                    ii.owner_guid,
                    ii.creatorGuid,
                    ii.giftCreatorGuid,
                    ii.`count` AS stack_count,
                    ii.duration,
                    ii.charges,
                    ii.flags,
                    ii.enchantments,
                    ii.randomPropertyId,
                    ii.durability,
                    ii.playedTime,
                    ii.text
                FROM characters c
                INNER JOIN character_inventory ci
                    ON ci.guid = c.guid
                INNER JOIN item_instance ii
                    ON ii.guid = ci.item
                WHERE c.account = :account_id
                    AND ii.itemEntry = :token_item_id
                ORDER BY c.guid ASC, ci.bag ASC, ci.slot ASC, ii.guid ASC
                FOR UPDATE",
                array(
                    'account_id' => $accountId,
                    'token_item_id' => self::get_token_item_id(),
                )
            )->fetchAllAssociative();

            $tokenCost = (int) $service['price'];
            $ledger = self::deduct_tokens_for_purchase($connection, $tokenRows, $tokenCost);
            $soapCommand = self::render_service_command($service['soap_command'], $account, $targetCharacter);

            $connection->executeStatement(
                'INSERT INTO ' . self::get_auth_order_table_reference(true) . ' (
                    account_id,
                    account_username,
                    account_email,
                    realm_id,
                    service_id,
                    service_title,
                    service_scope,
                    target_character_guid,
                    target_character_name,
                    token_item_id,
                    token_cost,
                    token_spend_ledger,
                    soap_command_template,
                    soap_command_snapshot,
                    status,
                    status_message
                ) VALUES (
                    :account_id,
                    :account_username,
                    :account_email,
                    :realm_id,
                    :service_id,
                    :service_title,
                    :service_scope,
                    :target_character_guid,
                    :target_character_name,
                    :token_item_id,
                    :token_cost,
                    :token_spend_ledger,
                    :soap_command_template,
                    :soap_command_snapshot,
                    :status,
                    :status_message
                )',
                array(
                    'account_id' => $accountId,
                    'account_username' => (string) $account['username'],
                    'account_email' => (string) $account['email'],
                    'realm_id' => self::get_realm_id(),
                    'service_id' => $service['id'],
                    'service_title' => $service['title'],
                    'service_scope' => $service['scope'],
                    'target_character_guid' => $targetCharacter ? (int) $targetCharacter['guid'] : null,
                    'target_character_name' => $targetCharacter ? (string) $targetCharacter['name'] : null,
                    'token_item_id' => self::get_token_item_id(),
                    'token_cost' => $tokenCost,
                    'token_spend_ledger' => json_encode($ledger),
                    'soap_command_template' => $service['soap_command'],
                    'soap_command_snapshot' => $soapCommand,
                    'status' => 'processing',
                    'status_message' => lang_or('store_processing', 'Store checkout is being processed.'),
                )
            );

            $orderId = (int) $connection->lastInsertId();
            $connection->commit();

            return array(
                'order_id' => $orderId,
                'account_id' => $accountId,
                'soap_command' => $soapCommand,
                'ledger' => $ledger,
            );
        } catch (Throwable $exception) {
            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }

            throw $exception;
        }
    }

    private static function resolve_target_character($service, $lockedCharacters, $targetCharacterGuid)
    {
        if ($service['scope'] === 'account') {
            return null;
        }

        if ($targetCharacterGuid < 1) {
            throw new store_user_exception(lang_or('store_character_required', 'Choose a character for this service.'));
        }

        foreach ($lockedCharacters as $character) {
            if ((int) $character['guid'] === $targetCharacterGuid) {
                return $character;
            }
        }

        throw new store_user_exception(lang_or('store_character_not_found', 'The selected character is not available on this account.'));
    }

    private static function deduct_tokens_for_purchase($connection, $tokenRows, $tokenCost)
    {
        $availableTokens = 0;
        foreach ($tokenRows as $tokenRow) {
            $availableTokens += (int) $tokenRow['stack_count'];
        }

        if ($availableTokens < $tokenCost) {
            throw new store_user_exception(lang_or('store_not_enough_tokens', 'You do not have enough Grim Tokens for that service.'));
        }

        $remainingCost = $tokenCost;
        $ledger = array();

        foreach ($tokenRows as $tokenRow) {
            if ($remainingCost < 1) {
                break;
            }

            $rowCount = (int) $tokenRow['stack_count'];
            if ($rowCount < 1) {
                continue;
            }

            $deductedCount = min($rowCount, $remainingCost);
            $remainingCost -= $deductedCount;
            $newCount = $rowCount - $deductedCount;

            $ledger[] = array(
                'character_guid' => (int) $tokenRow['character_guid'],
                'character_name' => (string) $tokenRow['character_name'],
                'bag' => (int) $tokenRow['bag'],
                'slot' => (int) $tokenRow['slot'],
                'item_guid' => (int) $tokenRow['item_guid'],
                'item_entry' => (int) $tokenRow['itemEntry'],
                'owner_guid' => (int) $tokenRow['owner_guid'],
                'creator_guid' => (int) $tokenRow['creatorGuid'],
                'gift_creator_guid' => (int) $tokenRow['giftCreatorGuid'],
                'original_count' => $rowCount,
                'deducted_count' => $deductedCount,
                'duration' => (int) $tokenRow['duration'],
                'charges' => $tokenRow['charges'],
                'flags' => (int) $tokenRow['flags'],
                'enchantments' => (string) $tokenRow['enchantments'],
                'random_property_id' => (int) $tokenRow['randomPropertyId'],
                'durability' => (int) $tokenRow['durability'],
                'played_time' => (int) $tokenRow['playedTime'],
                'text' => $tokenRow['text'],
            );

            if ($newCount > 0) {
                $connection->executeStatement(
                    'UPDATE item_instance SET `count` = :new_count WHERE guid = :item_guid',
                    array(
                        'new_count' => $newCount,
                        'item_guid' => (int) $tokenRow['item_guid'],
                    )
                );
            } else {
                $connection->executeStatement(
                    'DELETE FROM character_inventory WHERE item = :item_guid',
                    array('item_guid' => (int) $tokenRow['item_guid'])
                );
                $connection->executeStatement(
                    'DELETE FROM item_instance WHERE guid = :item_guid',
                    array('item_guid' => (int) $tokenRow['item_guid'])
                );
            }
        }

        if ($remainingCost > 0) {
            throw new store_user_exception(lang_or('store_checkout_internal_error', 'The store encountered an unexpected error and needs review.'));
        }

        return $ledger;
    }

    private static function refund_purchase($purchaseContext)
    {
        $connection = self::get_chars_connection();
        if ($connection === false) {
            throw new RuntimeException('Character database connection is not available.');
        }

        $connection->beginTransaction();

        try {
            foreach ($purchaseContext['ledger'] as $entry) {
                $existingCount = $connection->executeQuery(
                    'SELECT `count` FROM item_instance WHERE guid = :item_guid FOR UPDATE',
                    array('item_guid' => (int) $entry['item_guid'])
                )->fetchOne();

                if ($existingCount !== false) {
                    $connection->executeStatement(
                        'UPDATE item_instance SET `count` = `count` + :refund_count WHERE guid = :item_guid',
                        array(
                            'refund_count' => (int) $entry['deducted_count'],
                            'item_guid' => (int) $entry['item_guid'],
                        )
                    );
                    continue;
                }

                $connection->executeStatement(
                    'INSERT INTO item_instance (
                        guid,
                        itemEntry,
                        owner_guid,
                        creatorGuid,
                        giftCreatorGuid,
                        count,
                        duration,
                        charges,
                        flags,
                        enchantments,
                        randomPropertyId,
                        durability,
                        playedTime,
                        text
                    ) VALUES (
                        :guid,
                        :item_entry,
                        :owner_guid,
                        :creator_guid,
                        :gift_creator_guid,
                        :count,
                        :duration,
                        :charges,
                        :flags,
                        :enchantments,
                        :random_property_id,
                        :durability,
                        :played_time,
                        :text
                    )',
                    array(
                        'guid' => (int) $entry['item_guid'],
                        'item_entry' => (int) $entry['item_entry'],
                        'owner_guid' => (int) $entry['owner_guid'],
                        'creator_guid' => (int) $entry['creator_guid'],
                        'gift_creator_guid' => (int) $entry['gift_creator_guid'],
                        'count' => (int) $entry['original_count'],
                        'duration' => (int) $entry['duration'],
                        'charges' => $entry['charges'],
                        'flags' => (int) $entry['flags'],
                        'enchantments' => (string) $entry['enchantments'],
                        'random_property_id' => (int) $entry['random_property_id'],
                        'durability' => (int) $entry['durability'],
                        'played_time' => (int) $entry['played_time'],
                        'text' => $entry['text'],
                    )
                );

                $connection->executeStatement(
                    'INSERT INTO character_inventory (guid, bag, slot, item) VALUES (:guid, :bag, :slot, :item_guid)',
                    array(
                        'guid' => (int) $entry['character_guid'],
                        'bag' => (int) $entry['bag'],
                        'slot' => (int) $entry['slot'],
                        'item_guid' => (int) $entry['item_guid'],
                    )
                );
            }

            $connection->commit();
        } catch (Throwable $exception) {
            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }

            throw $exception;
        }
    }

    private static function render_service_command($template, $account, $targetCharacter = null)
    {
        $replacements = array(
            '{ACCOUNT_ID}' => (string) (int) $account['id'],
            '{ACCOUNT_USERNAME}' => (string) $account['username'],
            '{ACCOUNT_EMAIL}' => (string) $account['email'],
            '{TARGET_CHARACTER}' => $targetCharacter ? (string) $targetCharacter['name'] : '',
            '{TARGET_GUID}' => $targetCharacter ? (string) (int) $targetCharacter['guid'] : '',
            '{REALM_ID}' => (string) self::get_realm_id(),
        );

        return strtr($template, $replacements);
    }

    private static function ensure_order_table()
    {
        if (self::$orderTableReady) {
            return true;
        }

        database::$auth->executeStatement(
            'CREATE TABLE IF NOT EXISTS ' . self::get_auth_order_table_reference(false) . ' (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                account_id int(10) unsigned NOT NULL,
                account_username varchar(255) NOT NULL,
                account_email varchar(255) NOT NULL,
                realm_id int(10) unsigned NOT NULL,
                service_id varchar(64) NOT NULL,
                service_title varchar(255) NOT NULL,
                service_scope varchar(32) NOT NULL,
                target_character_guid int(10) unsigned DEFAULT NULL,
                target_character_name varchar(255) DEFAULT NULL,
                token_item_id int(10) unsigned NOT NULL,
                token_cost int(10) unsigned NOT NULL,
                token_spend_ledger longtext NOT NULL,
                soap_command_template text NOT NULL,
                soap_command_snapshot text NOT NULL,
                status varchar(32) NOT NULL,
                status_message text DEFAULT NULL,
                created_at datetime NOT NULL DEFAULT current_timestamp(),
                fulfilled_at datetime DEFAULT NULL,
                refunded_at datetime DEFAULT NULL,
                updated_at datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                PRIMARY KEY (id),
                KEY idx_store_service_orders_account_created (account_id, created_at),
                KEY idx_store_service_orders_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );

        self::$orderTableReady = true;
        return true;
    }

    private static function update_order_status($orderId, $status, $statusMessage, $timestampField = null)
    {
        self::ensure_order_table();

        $setClauses = array(
            'status = :status',
            'status_message = :status_message',
        );
        if ($timestampField === 'fulfilled_at') {
            $setClauses[] = 'fulfilled_at = CURRENT_TIMESTAMP()';
        } elseif ($timestampField === 'refunded_at') {
            $setClauses[] = 'refunded_at = CURRENT_TIMESTAMP()';
        }

        database::$auth->executeStatement(
            'UPDATE ' . self::get_auth_order_table_reference(false) . ' SET ' . implode(', ', $setClauses) . ' WHERE id = :id',
            array(
                'status' => $status,
                'status_message' => $statusMessage,
                'id' => (int) $orderId,
            )
        );
    }

    private static function get_chars_connection()
    {
        $realmId = self::get_realm_id();
        if (empty(database::$chars[$realmId])) {
            return false;
        }

        return database::$chars[$realmId];
    }

    private static function get_realm_id()
    {
        $realmlists = get_config('realmlists');
        if (!is_array($realmlists) || empty($realmlists)) {
            return 1;
        }

        $firstRealm = reset($realmlists);
        if (is_array($firstRealm) && !empty($firstRealm['realmid'])) {
            return (int) $firstRealm['realmid'];
        }

        return (int) key($realmlists);
    }

    private static function get_token_item_id()
    {
        $tokenItemId = (int) get_config('store_token_item_id');
        if ($tokenItemId < 1) {
            $tokenItemId = 90000;
        }

        return $tokenItemId;
    }

    private static function get_auth_order_table_reference($qualifyDatabase)
    {
        $tableName = self::quote_identifier(self::ORDER_TABLE);
        if (!$qualifyDatabase) {
            return $tableName;
        }

        return self::quote_identifier((string) get_config('db_auth_dbname')) . '.' . $tableName;
    }

    private static function quote_identifier($identifier)
    {
        return '`' . str_replace('`', '``', (string) $identifier) . '`';
    }

    private static function clear_portal_session()
    {
        unset($_SESSION[self::SESSION_ACCOUNT_ID], $_SESSION[self::SESSION_USERNAME], $_SESSION[self::SESSION_EMAIL]);
    }

    private static function extract_account_id($account)
    {
        if (is_array($account) && !empty($account['id'])) {
            return (int) $account['id'];
        }

        return (int) $account;
    }

    private static function get_account_lock_name($accountId)
    {
        return 'store-checkout-' . self::get_realm_id() . '-' . (int) $accountId;
    }

    private static function acquire_account_lock($lockName)
    {
        $lockResult = database::$auth->executeQuery(
            'SELECT GET_LOCK(:lock_name, 5)',
            array('lock_name' => $lockName)
        )->fetchOne();

        return (string) $lockResult === '1';
    }

    private static function release_account_lock($lockName)
    {
        if ($lockName === '') {
            return false;
        }

        database::$auth->executeQuery(
            'SELECT RELEASE_LOCK(:lock_name)',
            array('lock_name' => $lockName)
        );

        return true;
    }

    private static function summarize_soap_result($soapResult, $fallback)
    {
        $message = trim((string) ($soapResult['message'] ?? ''));
        if ($message === '') {
            return $fallback;
        }

        return $message;
    }
}
