Feature: car API
    in order to manage cars
    as a product manager
    I need to be able to create new cars and view a car list

    Scenario: Creating new car
        Given I have the payload:
        """
        {"make": "Audi", "model": "VM300", "catalogPrice": 20000000}
        """
        When I request "POST /car"
        Then the response property "carId" equals "1"

    Scenario: View cars list
        When I request "GET /car"
        Then the response property "cars.0.id" equals "1"

    Scenario: View lease list
        When I request "GET /car/lease/12/100"
        Then the response property "cars.0.leasePrice" equals "111111"
    
    Scenario: try to create duplicate
        Given I have the payload:
        """
        {"make": "Audi", "model": "VM300", "catalogPrice": 20000000}
        """
        When I request "POST /car"
        Then the response has error 'Car "Audi" "VM300" already exists'