<?php

namespace BrewMe\Controller;

class OrderController {

    public function post()
    {
        //Grab the post data
        $post = htmlspecialchars($_POST['text'], ENT_QUOTES);

        $order = explode(" ", $post);

        switch ($order[0]) {
            case '-help':
                return $this->getHelp();
                break;
            case 'order':
                return $this->storeOrder();
                break;
            default:
                return $this->getHelp();
        }
    }

    private function storeOrder()
    {
        return "Order";
    }

    /**
     * Returns the help command list
     *
     * @return false|string
     */
    private function getHelp()
    {
        $help = "
        Welcome to BrewMe!
        - The command format for brew ordering is `/brew prepare {brew}:{extra}-{extra}`
        Where `{brew}` Coffee or Tea
              `{extra}` Milk or Sugar
        - The command format for fridge ordering is `/brew grab {item}
        Where `{item]` is just an item from the fridge
        - The command format for ordering your default brew is `/brew order-default`
        - The command format for setting your default brew is `/brew set-default {brew}:{extra}-{extra}`
          Where `{brew}` Coffee or Tea
              `{extra}` Milk or Sugar
        ";
        return json_encode([
            'response_type' => "in_channel",
            "text" => "BrewMe Awesome Documentation",
            "attachments" => [
                [
                    "text" => $help
                ]
            ]
        ]);
    }
}