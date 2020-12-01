# Cookie Handling

In order to avoid dynamic cookie naming, in the name of compliance, while still providing flexibility, BlueSpiceCalumma
implements its own cookie handler wrapper.

By using this wrapper, code can set and read many cookies, but all of them will be set to Header as a single compound cookie.

## Client-side
To set cookies, client side use `bs.calumma.cookie` object.
Usage is the same as using `mw.cookie`

     bs.calumma.cookie.set( 'myCookie', 'value1' );
     bs.calumma.cookie.get( 'myCookie' );

Calling

     bs.calumma.cookie.set( 'myCookie', 'value1' );
     bs.calumma.cookie.set( 'myOtherCookie', true );

will produce this actual cookie

     cookieName: 'Calumma_state',
     cookieValue: {
        myCookie: 'value1',
        myOtherCookie: true
     }

## Backend
In order to access single cookies from the backend use `BlueSpice\Calumma\CookieHandler`.

     $cookieHandler = new BlueSpice\Calumma\CookieHandler( $request ); // Pass \WebRequest
     $cookieHandler->getCookie( 'myCookie' );
     $cookieHandler->getCookie( 'myOtherCookie' );

cookies can be set in a similar way

     $cookieHandler = new BlueSpice\Calumma\CookieHandler( $request ); // Pass \WebRequest
     $cookieHandler->setCookie( 'myCookie', 'myValue' );
