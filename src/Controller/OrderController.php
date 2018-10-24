<?php

namespace BrewMe\Controller;

use BrewMe\CFG;
use BrewMe\DBI\UserDBI;
use BrewMe\DBI\OrderDBI;
use BrewMe\Model\Order;
use BrewMe\Model\User;

class OrderController extends BaseController {

    /**
     * The available commands
     */
    const COMMANDS = [
        'make',
        'grab',
        'list',
        'set',
        'done',
        'cancel',
        'help',
    ];

    /**
     * The available brews
     */
    const DRINKS = [
        'coffee',
        'tea'
    ];

    /**
     * The user arguments
     * @var array
     */
    private $args;

    /**
     * The command user typed
     * @var null
     */
    private $command = null;

    /**
     * The comments user typed
     * @var null
     */
    private $comments = null;

    /**
     * The type of the drink user selected
     * @var null
     */
    private $type = null;


    /**
     * Representing user requesting brewme
     * @var User|null
     */
    private $user = null;

    /**
     * Return a string with a random prefix
     * 
     * @return string
     */
    private function getRandomCheer()
    {
        $strings = [
            'Whoop Whoop! ',
            'Yayyy! ',
            'Awesome! ',
            'Woohooo! '
        ];

        return $strings[rand(0,3)];
    }

    /**
     * Handle the post request
     *
     * @return bool|false|string
     */
    public function post()
    {
        //Validate the post and grab the arguments
        if ($this->validateInput()) {
            return $this->validateInput();
        }

        switch ($this->args[0]) {
            case 'make':
                return $this->storeOrder();
                break;
            case 'grab':
                return $this->grabDrink();
                break;
            case 'list':
                return $this->listOrders();
                break;
            case 'set':
                return $this->setOrder();
                break;
            case 'done':
                return $this->done();
            case 'help':
                return $this->getHelp();
                break;
            default:
                return $this->getHelp();
        }
    }

    private function confirmUser($username)
    {
        $user = UserDBI::findUserByUsername($username);

        if ($user) {
            $this->user = new User($user);
        } else {
            // Create new user
            $userId = UserDBI::createUser([
                'username' => $username
            ]);
            $this->user = new User([
                'id' => $userId,
                'username' => $username
            ]);
        }

        return $this->user;
    }

    private function storeOrder()
    {
        $key = CFG::get("SLACK_OAUTH_ACCESS_TOKEN");
        //Fetch the user
        $username = $_POST['user_name'];

        $this->confirmUser($username);

        // Check is user has outstanding orders
        $pendingOrder = OrderDBI::getOrdersByUserIdAndStatus($this->user->id, Order::STATUS_PENDING);
        if ($pendingOrder) {
            $pendingOrder = array_shift($pendingOrder);
            return $this->respond("You already have an order pending for " . $pendingOrder['type'] . " :seriouscat:");
        }

        $orderId = OrderDBI::createOrder([
            'user_id' => $this->user->id,
            'type' => $this->type,
            'comments' => $this->comments,
            'status' => Order::STATUS_PENDING
        ]);

        $msg = $this->getRandomCheer();
        $msg .= "Your {$this->type} " . ($this->comments ? "with " . $this->comments  : '') . " ";
        $msg .= "is on the way!";  

        return $this->respond($msg);
    }

    private function grabDrink()
    {
        return $this->respond("Todo...");
    }

    private function listOrders()
    {
        $orders = OrderDBI::getOrdersByStatus(Order::STATUS_PENDING);
        if (!$orders) {
            return $this->respond("Not a single brew to do... :canttouchthis:");
        }

        return $this->respond(count($orders) . " brews due...\n" . $this->ordersToSlackResponse($orders));
    }

    private function ordersToSlackResponse(array $orders)
    {
        $msg = '';
        foreach ($orders as $order) {
            $msg .= $order['username'] . " ordered " . $order['type'];
            $msg .= $order['comments'] ? " with " . $order['comments'] : '';
            $msg .= "\n";
        }
        return $msg;
    }

    private function setOrder()
    {
        return "Setting Order";
    }

    private function done()
    {
        OrderDBI::changeOrdersStatus(Order::STATUS_PENDING, Order::STATUS_DONE);
        return 'All outstanding orders marked done.';
    }

    /**
     * Validate the user's input and store the args
     */
    private function validateInput()
    {
        //Grab the post data
        $post = htmlspecialchars($_POST['text'], ENT_QUOTES);

        //Seperate arguments and comments and make run some validations
        $parts          = explode(":", $post);
        $this->args     = explode(" ", $parts[0]);
        $this->command  = trim($this->args[0]);
        $this->type     = trim($this->args[1]);
        $this->comments = $parts[1] ? trim($parts[1]) : null;

        if (empty($this->command)) {
            $msg = 'Welcome to BrewMe. Type `/brew help` for the full list of all valid commands';
            return $this->respond($msg);
        }
        else if (!in_array($this->command, self::COMMANDS)) {
            $msg = '`' . $this->command . '` is not a valid command. Type `brew help` for the full list of valid commands';
            return $this->respond($msg);
        }

        if ($this->command === 'make') {
            if (empty($this->type)) {
                $msg = 'You must declare the type of the drink. Type `/brew help` for the full list of all valid commands';
                return $this->respond($msg);
            }
            else if (!in_array($this->type, self::DRINKS)) {
                $msg = 'Sorry but we don\'t currently serve `' . $this->type . '`. Type `brew help` for the full list of valid commands and drinks.';
                return $this->respond($msg);
            }
        }
        return false;
    }

    /**
     * Returns the help command list
     *
     * @return false|string
     */
    private function getHelp()
    {
        $msg = "
        We need to write the help part
        ";
        return $this->respond($msg);
    }

    /**
     * Returns a respond
     *
     * @param string $msg
     * @return false|string
     */
    private function respond($msg = "There was a problem. Please try again or type `/brew help`")
    {
        return json_encode([
            'response_type' => "in_channel",
            "text" => "Brew ordering system powered by Buildempire",
            "attachments" => [
                [
                    "text" => $msg,
                    "color" => "#058e5d"
                ]
            ]
        ]);
    }
}