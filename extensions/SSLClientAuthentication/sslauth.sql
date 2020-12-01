-- Database Schema for Client SSL Authentication

CREATE TABLE /*_*/sslcerts (
    -- ID for the table
    ssl_id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,

    -- DN of the certificate's issuer
    ssl_issuer mediumblob NOT NULL,

    -- Serial number for the certificate (should be unique for
    -- each certificate issued by a given CA).
    ssl_serial varbinary(20) NOT NULL,

    -- User who owns the certificate. Foreign key to user table.
    ssl_user_id INT UNSIGNED NOT NULL,
    
    -- CN value on the certificate.
    ssl_cn varbinary(255) NOT NULL,

    -- Email value on the certificate.
    ssl_email tinyblob NOT NULL,

    -- Store the actual PEM encoded certificate. This is just
    -- in case PKI fails and an attacker is able to make a
    -- different certificate with the same issuer and serial.
    ssl_certificate mediumblob NOT NULL
);

-- Index on issuer and serial.
CREATE UNIQUE INDEX /*i*/sslcerts_id ON /*_*/sslcerts (ssl_issuer(50), ssl_serial);

-- Index on user IDs when enabling/disabling SSL authentication.
CREATE UNIQUE INDEX /*i*/sslcerts_users ON /*_*/sslcerts (ssl_user_id);
