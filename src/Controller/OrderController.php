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
        $validationErrors = $this->validateInput();
        if ($validationErrors) {
            return $validationErrors;
        }

        switch ($this->args[0]) {
            case 'make':
                return $this->storeOrder();
                break;
            case 'grab':
                return $this->storeOrder();
                break;
            case 'list':
                return $this->listOrders();
                break;
            case 'set':
                return $this->setPreferences();
                break;
            case 'done':
                return $this->done();
            case 'cancel':
                return $this->cancel();
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

    private function checkPendingOrders(){
        return OrderDBI::getOrdersByUserIdAndStatus($this->user->id, Order::STATUS_PENDING) ?? false;
    }

    private function storeOrder()
    {
        $username = $_POST['user_name'];

        $this->confirmUser($username);

        // Check is user has outstanding orders
        $pendingOrder = $this->checkPendingOrders();
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

    private function listOrders()
    {
        $orders = OrderDBI::getOrdersByStatus(Order::STATUS_PENDING);
        if (!$orders) {
            return $this->respond("Not a single brew to do... :canttouchthis:");
        }


        return $this->respond(count($orders) . " brew". (count($orders) > 1 ? 's' : '') ." due...\n" . $this->ordersToSlackResponse($orders));
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

    private function setPreferences()
    {
        $this->confirmUser($_POST['user_name']);

        // Delete current preferences
        UserDBI::upsertUserPreferences([
            'user_id' => $this->user->id,
            'type' => $this->type,
            'comments' => $this->comments
        ]);

        return "Preferences set!";
    }

    private function done()
    {
        OrderDBI::changeOrdersStatus(Order::STATUS_PENDING, Order::STATUS_DONE);
        return 'All outstanding orders marked done.';
    }

    private function cancel()
    {
        $this->confirmUser($_POST['user_name']);
        // Check is user has outstanding orders
        $pendingOrders = OrderDBI::getOrdersByUserIdAndStatus($this->user->id, Order::STATUS_PENDING);
        if ($pendingOrders) {
            foreach ($pendingOrders as $pendingOrder) {
                OrderDBI::changeOrderStatus($pendingOrder['id'], Order::STATUS_CANCELLED);
            }
        }
        return $this->respond("Your order has been canceled :glitch_crab:");
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

        if ($this->command === 'make' || $this->command === 'set') {
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
        $text = "BrewMe is a brew ordering system slack app powered by Buildempire.\n";
        $text .= "Available brews are `coffee` and `tea` but you can order anything else with the `grab` command\n";
        $text .= "You can have only 1 pending order. Our UK servers can\'t handle that tea consumption.\n";
        $text .= "Available commands:\n";
        $text .= "-Ordering a brew format is `/brew make {type}:{comments}`\n";
        $text .= "-Ordering an item format is `/brew grab {item}\n";
        $text .= "-Setting your default brew format is `/brew set {type}:{comments}`\n";
        $text .= "-Getting the list of all orders format is `/brew list`\n";
        $text .= "-Cancelling your order format is `/brew cancel`\n";
        $text .= "-Getting BrewMe documentation and info format is `/brew help`. Obvious";

        return json_encode([
            'response_type' => "in_channel",
            "attachments" => [
                [
                    "pretext" => "BrewMe Information & Help",
                    "author_name" => "Michal & Vagelis Winners of Hackday2018",
                    "title" => "BrewMe Documentation",
                    "title_link" => "https://github.com/BuildEmpire/HackDay18_BrewMe",
                    "text" => $text,
                    "color" => "#058e5d",
                    "footer" =>"Brew ordering system powered by Buildempire",
                    "footer_icon" =>"https://buildempire.co.uk/wp-content/themes/buildempire2016/favicon-32x32.png?v=2",
                ]
            ]
        ]);
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
            "attachments" => [
                [
                    "text" => $msg,
                    "color" => "#058e5d",
                    "footer" =>"Brew ordering system powered by Buildempire",
                    "footer_icon" =>"https://buildempire.co.uk/wp-content/themes/buildempire2016/favicon-32x32.png?v=2",
                ]
            ]
        ]);
    }
}