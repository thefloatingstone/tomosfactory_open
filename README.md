API Factomos V1.20 - 15/04/2015
==============================

# 1. Introduction

Toutes les requêtes doivent se faire en HTTPS vers l'URL: https://app.factomos.com/api/api.php

cette URL ne prend que deux paramètres token et crequest en POST
crequest étant la chaîne cryptée représentant la suite des paramètres POST

Pour crypter cette chaîne il faut utiliser le paramètre secret qui doit absolument rester confidentiel.

Pour l'utilisation de l'API, Factomos vous fournit donc le couple

token: xxxxxx
secret: yyyyyy

Fonction de cryptage / décryptage en PHP : 

```php
function factomos_encode($secretKey, $str) {
    return urlencode(base64_encode(mcrypt_encrypt(
            MCRYPT_RIJNDAEL_128,
            md5($secretKey),
            $str,
            MCRYPT_MODE_CFB,
            $secretKey
    )));
}

function factomos_decode($secretKey, $str, $urldecode = false) {
    if($urldecode) {
        $str = urldecode($str);
    }
    return mcrypt_decrypt(
            MCRYPT_RIJNDAEL_128,
            md5($secretKey),
            base64_decode($str),
            MCRYPT_MODE_CFB,
            $secretKey
    );
}

```

$key correspond au paramètre secret et $str à la chaîne cryptée qu'il faut passer dans le champ (POST) crequest.

Exemple de requête :
POST vers
https://app.factomos.com/api/api.php

avec les paramètres suivants:

 - token=xxxxxx
 - crequest=dfgsdfjghsd

ou dfgsdfjghsd serait le cryptage de la chaîne suivante : action=getClient&client_id=766

Le retour se fait au format JSON (une fois qu'il est décrypté)
Exemple de retour :
```json
{
    "error": {
        "code": -1,
        "message": "Missing Token"
    }
}
```

## Codes d'Erreur génériques

Code d'erreur | Message d'erreur                          | Description
------------: | :---------------------------------------- | :----------------
0             |                                           | Pas d'erreur, la requête s'est bien passée
-1            | Missing Token                             | Le Champ "token" est manquant, or il est obligatoire
-2            | Missing Crypted Request                   | Le Champ "crequest" est manquant, or il est obligatoire
-3            | Invalid Token                             | Le Token est invalide, il n'existe pas dans la base Factomos
-4            | Too many API calls for the day            | Vous avez dépassé le nombre maximum d'appels API pour la journée (par défaut limité à 500)
-5            | Invalid Crypted Request                   | La requête n'a pas pu être décryptée, le champ crequest est invalide
-6            | Action not found or invalid               | Le Champ "action" est manquant, or il est obligatoire

## Page de test

Url: https://app.factomos.com/api/test/
Cette page permet de tester tous les appels API et voir les retours


# 2. Création de compte Factomos via l'API

## Paramètres en entrée

POST REQUEST
 - action=createAccount (OBLIGATOIRE)
 - username, Identifiant du compte Factomos, ce doit être une adresse email valide (OBLIGATOIRE)
 - password, Mot de passe, (OPTIONNEL, si pas présendev.cron.dailyt un mot de passe sera généré aléatoirement)
 - name, Nom de la société, (OPTIONNEL)
 - company_type, Structure juridique parmi : (OPTIONNEL)
    - Agessa
    - Association
    - Auto-Entrepreneur
    - E.U.R.L.
    - Entreprise-Individuelle
    - MDA
    - Profession-Liberale
    - S.A.
    - S.A.R.L.
    - S.A.S.
    - S.A.S.U.
    - S.C.I.
    - S.E.L.A.R.L.
 - company_address, Adresse de la société (OPTIONNEL)
 - zipcode, Code postal de la société (OPTIONNEL)
 - city, Ville de la société (OPTIONNEL)
 - country, Pays de la société, ex: France (OPTIONNEL)
 - phone_number, Téléphone de la société (OPTIONNEL)
 - fax_number, Fax de la société (OPTIONNEL)
 - company_email, Email de la société (OPTIONNEL, si pas présent, alors l'email sera copié du champ username)
 - company_website, Site web de la société (OPTIONNEL)
 - capital, Capital de la société, ex: 5000€ (OPTIONNEL)
 - siret, Numéro de siret de la société (OPTIONNEL)
 - vat_percentage, Taux de TVA par défaut, ex: 20 (OPTIONNEL, si pas présent le taux sera 0)
 - vat_number, Numéro de TVA intra-communautaire (OPTIONNEL)
 - tva_enable, Activation de la TVA (0/1) (OPTIONNEL, si pas présent égale à 0)

On peut aussi rajouter les paramètres suivants pour créer un couple token/secret API pour ce user spécifique

 - app_name: le nom de l’appli
 - app_description: une courte description de l’appli.
 - app_icon: URL de l'icône de l'application (40px x 40px), optionnel
 - webhook_url: URL qui va être notifiée via un POST (cf # 6.) à chaque action faite dans Factomos

## Paramètres en sortie

 - company_id, l'identifiant de la société,
 - password, si aucun mot de passe n'était présent dans la requête alors celui-ci sera présent avec une valeur générée aléatoirement
 - auth_token, Token d'authentification utilisable une fois (cf # 3.)

Si des paramètres API ont été rajoutés à la requête alors on aura les paramètres suivants dans la réponse:

 - api_key, Clé API (token)
 - api_secret, Clé secrète (pour crypter / décrypter les requêtes API)

## Code d'erreurs

Code d'erreur | Message d'erreur                          | Description
------------: | :---------------------------------------- | :----------------
0             |                                           | Pas d'erreur, la requête s'est bien passée
-1            | Missing Token                             | Le Champ "token" est manquant, or il est obligatoire
-2            | Missing Crypted Request                   | Le Champ "crequest" est manquant, or il est obligatoire
-3            | Invalid Token                             | Le Token est invalide, il n'existe pas dans la base Factomos
-4            | Too many API calls for the day            | Vous avez dépassé le nombre maximum d'appels API pour la journée (par défaut limité à 500)
-5            | Invalid Crypted Request                   | La requête n'a pas pu être décryptée, le champ crequest est invalide
-6            | Action not found or invalid               | Le Champ "action" est manquant, or il est obligatoire
-29           | Missing parameter username                | Le Champ "username" est manquant, or il est obligatoire
-30           | Username not available                    | L'identifiant n'est pas disponible car un compte Factomos existe déjà avec ce dernier

## Exemple simple

POST REQUEST
 - action=createAccount
 - username=mail@mail.com
 - name=World Company
 - company_type=S.A.R.L.

JSON RESULT
```json
{
    "company_id":"1",
    "password":"my_password_generated",
    "auth_token":"MY_TOKEN_GENERATED",
    "error":{
        "code":0,
        "message":"OK"
    }
}
```

## Exemple avec des paramètres API

POST REQUEST
 - action=createAccount
 - username=mail@mail.com
 - name=World Company
 - company_type=S.A.R.L.
 - app_name=My Application
 - app_description=CRM Application very simple
 - app_icon=http://myapplication.com/icon.png
 - webhook_url=http://myapplication.com/web_hook.php

JSON RESULT
```json
{
    "company_id":"1",
    "password":"my_password_generated",
    "auth_token":"MY_TOKEN_GENERATED",
    "api_key":"MY_API_KEY_GENERATED",
    "api_secret":"MY_API_SECRET_GENERATED",
    "error":{
        "code":0,
        "message":"OK"
    }
}
```

# 3. Création de session sur Factomos.com via l'API (SSO)

## Paramètres en entrée

POST REQUEST
 - action=getConnectionToken (OBLIGATOIRE)

## Paramètres en sortie

 - auth_token, token d'authentification valable une seule fois

Ce token d'authentification peut être utilisé de deux manières

1. dans l'entête HTTP "x-auth-token", ce qui permet par exemple de faire un POST pour créer un devis ou une facture en étant directement connecté
2. dans une requête HTTP POST vers `https://app.factomos.com/api/session/create/<auth_token>`, ce qui va renvoyer un JSON comme suit:

JSON RESULT
```json
{
    "error":{
        "code":0,
        "message":"OK"
    }
}
```

La session ainsi créée est détruite au bout de 6 heures d'inactivité, ou en faisant un HTTP POST vers `https://app.factomos.com/api/session/delete/<auth_token>`


## Code d'erreurs du CREATE SESSION ou DELETE SESSION `https://app.factomos.com/api/session/(create/delete)/<auth_token>`

Code d'erreur | Message d'erreur                          | Description
------------: | :---------------------------------------- | :----------------
0             |                                           | Pas d'erreur, la requête s'est bien passée
-31           | Missing auth_token                        | Le Champ "auth_token" est manquant, or il est obligatoire
-32           | Invalid auth_token                        | Le auth_token est invalide, il n'existe pas dans la base Factomos

## Code d'erreurs du getConnectionToken

Code d'erreur | Message d'erreur                                     | Description
------------: | :--------------------------------------------------- | :----------------
0             |                                                      | Pas d'erreur, la requête s'est bien passée
-1            | Missing Token                                        | Le Champ "token" est manquant, or il est obligatoire
-2            | Missing Crypted Request                              | Le Champ "crequest" est manquant, or il est obligatoire
-3            | Invalid Token                                        | Le Token est invalide, il n'existe pas dans la base Factomos
-4            | Too many API calls for the day                       | Vous avez dépassé le nombre maximum d'appels API pour la journée (par défaut limité à 500)
-5            | Invalid Crypted Request                              | La requête n'a pas pu être décryptée, le champ crequest est invalide
-6            | Action not found or invalid                          | Le Champ "action" est manquant, or il est obligatoire
-28           | Your Token is not associated with a Factomos account | Le token utilisé n'es pas associé à un compte Factomos

## Exemple en mode normal

POST REQUEST
 - action=getConnectionToken
 - mode=normal

JSON RESULT
```json
{
    "auth_token":"MY_TOKEN_GENERATED",
    "error":{
        "code":0,
        "message":"OK"
    }
}
```


# 4. Contrôle de la session en cours (SSO)

HTTP GET vers l'URL https://app.factomos.com/api/session/check

JSON RESULT
```json
{
    "error":{
        "code":0,
        "message":""
    },
    "user":{
        "id":"568",
        "username":"contact@factomos.com",
        "company_id":"550",
        "app_domain":"dev.factomos.com"
    }
}
```

## Code d'erreurs du CHECK SESSION

Code d'erreur | Message d'erreur                          | Description
------------: | :---------------------------------------- | :----------------
0             |                                           | Pas d'erreur, la requête s'est bien passée
-34           | No session                                | Il n'y a pas de session en cours

## Code d'erreurs du getConnectionToken

Code d'erreur | Message d'erreur                                     | Description
------------: | :--------------------------------------------------- | :----------------
0             |                                                      | Pas d'erreur, la requête s'est bien passée
-1            | Missing Token                                        | Le Champ "token" est manquant, or il est obligatoire
-2            | Missing Crypted Request                              | Le Champ "crequest" est manquant, or il est obligatoire
-3            | Invalid Token                                        | Le Token est invalide, il n'existe pas dans la base Factomos
-4            | Too many API calls for the day                       | Vous avez dépassé le nombre maximum d'appels API pour la journée (par défaut limité à 500)
-5            | Invalid Crypted Request                              | La requête n'a pas pu être décryptée, le champ crequest est invalide
-6            | Action not found or invalid                          | Le Champ "action" est manquant, or il est obligatoire
-28           | Your Token is not associated with a Factomos account | Le token utilisé n'es pas associé à un compte Factomos

## Exemple en mode normal

POST REQUEST
 - action=getConnectionToken
 - mode=normal

JSON RESULT
```json
{
    "auth_token":"MY_TOKEN_GENERATED",
    "error":{
        "code":0,
        "message":"OK"
    }
}
```

# 5. Obtenir le couple token, secret, via le formulaire d'autorisation d'Applications tierces

Il faut créer un bouton du genre = « Connecter avec Factomos » qui doit faire l’action suivante :

```javascript
window.open('https://app.factomos.com/api/authorize.php?app_callback=https%3A%2F%2Fdev.factomos.com%2Fapi%2Ftest%2Fcallback.php&app_name=My%20Application&app_description=Ceci%20est%20mon%20application&webhook_url=xxxx', '', 'width=399, height=400')
```
Les paramètres dans l’URL sont les suivants :

- app_callback : une page chez vous qui va récupérer le token et la clé secrète via un POST (api_key, et api_secret)
- app_name: le nom de l’appli
- app_description: une courte description de l’appli.
- app_icon: URL de l'icône de l'application (40px x 40px), optionnel
- webhook_url: URL qui va être notifiée via un POST (cf # 6.) à chaque action faite dans Factomos

Ceci va ouvrir une popup d’authentification, si le login et le mot de passe sont corrects, alors FACTOMOS va faire un POST vers l’url de call_back en envoyant les paramètres api_key, et api_secret.
Ensuite le popup est fermé


# 6. Le webhook

A chaque action, un contenu JSON va être envoyé en RAW POST vers l'URL de webhook qui a été précisée lors de la connexion de l'appli tierce.

## Liste des notifications envoyées vers le webhook

INVOICE
 - "create_invoice" => "Création d'une facture",
 - "delete_invoice" => "Suppression d'une facture",
 - "update_invoice" => "Modification d'une facture",
 - "add_invoice_payment" => "Ajout d'un règlement",
 - "update_invoice_payment" => "Modification d'un règlement",
 - "delete_invoice_payment" => "Suppression d'un règlement",
 - "create_avoir" => "Création d'un avoir",
 - "send_invoice" => "Envoi facture par mail",

ESTIMATE
 - "create_estimate" => "Création d'un devis",
 - "update_estimate" => "Modification d'un devis",
 - "delete_estimate" => "Suppression d'un devis",
 - "send_estimate" => "Envoi d'un devis par mail",
 - "estimate_status_gained" => "Devis accepté",
 - "estimate_status_gainedwithoutinvoice" => "Devis accepté (sans créer de facture)",
 - "estimate_status_gainedwithacompte" => "Devis accepté (avec accompte)",
 - "estimate_status_lost" => "Devis refusé",
 - "estimate_status_pending" => "Devis en attente",

CONTACT
 - "create_contact" => "Ajout d'un contact",
 - "update_contact" => "Modification d'un contact",
 - "delete_contact" => "Suppression d'un contact",


SERVICE
 - "create_service" => "Ajout d'une prestation",
 - "update_service" => "Modification d'une prestation",
 - "archive_service" => "Archivage d'une prestation",
 - "delete_service" => "Suppression d'une prestation",
 - "update_stock" => "Modification du stock",

PROFIL
 - "create_template_email" => "Création template email",
 - "update_template_email" => "Modification template email",
 - "delete_template_email" => "Suppression template email",
 - "create_design" => "Création template design",
 - "update_design" => "Modification template design",
 - "delete_design" => "Suppression template design",

## Format du Json envoyé

```json
{
	"company_id":<company_id>,
	"notification_type":<notification_type>,
	"result":{
		"id":<id>,
		"error":{
			"code":<code>,
			"message":<message>
		}
	},
	"custom":<custom>,
}
```

- `company_id`, Id du compte Factomos
- `notification_type`, Une des actions de la liste précédente (create_invoice, create_estimate, ...)
- `result`, Objet comprenant le retour lié à l'action notifiée
- `result.id`, Id de l'objet concerné par l'action
- `result.error.code`, Code d'erreur lié à l'action, 0 si tout est OK
- `result.error.message`, Message d'erreur lié à l'action, "OK" si tout est OK
- `custom`, Ce champ est présent s'il a été ajouté lors de l'action

Dans le cas d'une transformation de devis en facture, le JSON comprend aussi le champ `result.invoice_id` qui correspond à l'id de la facture créée

## Exemple de notification

Voici un exemple de POST lors de la création d'un devis.

```json
{
	"company_id":"550",
	"notification_type":"create_estimate",
	"result":{
		"id":"5782",
		"error":{
			"code":0,
			"message":"OK"
		}
	},
	"custom":"id=3",
}
```


# 7. Afficher un formulaire de création de devis (au sein de factomos.com) pré-rempli

Faire un POST vers la page https://app.factomos.com/creer-devis avec les paramètres suivants :

- `client_id` id du client (ce champ est prioritaire par rapport au champ client_company_name
- `client_company_name` (société du client, Factomos va essayer de le retrouver automatiquement uniquement à l’identique)
- `estimate_attention` (Champ « à l'attention de »  du devis)
- `objet` (Champ « objet » du devis)
- `estimate_comment` (Champ « commentaire » du devis)
- `custom`, un champ pour y mettre vos variables, ce dernier sera renvoyé tel quel dans la notification au webhook

# 8. Récupérer les infos d'un client à partir de son id

## Paramètres en entrée
 - action=getClient, (OBLIGATOIRE)
 - client_id=<id_du_client>, (OBLIGATOIRE)
 
## Code d'erreurs

Code d'erreur | Message d'erreur                          | Description
------------: | :---------------------------------------- | :----------------
0             |                                           | Pas d'erreur, la requête s'est bien passée
-1            | Missing Token                             | Le Champ "token" est manquant, or il est obligatoire
-2            | Missing Crypted Request                   | Le Champ "crequest" est manquant, or il est obligatoire
-3            | Invalid Token                             | Le Token est invalide, il n'existe pas dans la base Factomos
-4            | Too many API calls for the day            | Vous avez dépassé le nombre maximum d'appels API pour la journée (par défaut limité à 500)
-5            | Invalid Crypted Request                   | La requête n'a pas pu être décryptée, le champ crequest est invalide
-6            | Action not found or invalid               | Le Champ "action" est manquant, or il est obligatoire
-8            | Missing parameter client_id               | Le Champ "client_id" est manquant, or il est obligatoire
-9            | Client not found                          | Le client avec cet id n'existe pas dans la base Factomos

## Exemple

POST REQUEST
- action=getClient
- client_id=67

JSON RESULT
```json
{
    "client": {
        "client_id":"5782",
        "company_id":"1",
        "client_company_name":"name from API",
        "client_business_category":"",
        "client_reference":"reference from API",
        "description":"description from API",
        "client_address":"address from API",
        "client_address_more":"address more from API",
        "client_zipcode":"zipcode from API",
        "client_city":"city from API",
        "client_country":"country from API",
        "number_tva":"tva from API",
        "number_siret":"siret from API",
        "client_website":"website from API",
        "client_email":"email from API",
        "client_contact_firstname":"",
        "client_contact_lastname":"contact from API",
        "client_phonenumber":"phone from API",
        "client_contact_title":"",
        "category":"client",
        "client_other":"",
        "client_lang":"en",
        "client_keyword":"keyword from API",
        "client_code_analytic":"9NAMEFRO"
    },
    "error":{
        "code":0,
        "message":"OK"
    }
}
```
    


# 9. Créer un client

## Paramètres en entrée

- action=createClient, (OBLIGATOIRE)
- category
- client_company_name, (OBLIGATOIRE)
- client_reference
- client_contact_lastname
- client_email
- client_phonenumber
- client_address
- client_address_more
- client_zipcode
- client_city
- client_country
- client_lang
- number_siret
- number_tva
- client_website
- client_keyword
- description

## Paramètres en sortie

 - client_id: l'identifiant du client

## Code d'erreurs

Code d'erreur | Message d'erreur                          | Description
------------: | :---------------------------------------- | :----------------
0             |                                           | Pas d'erreur, la requête s'est bien passée
-1            | Missing Token                             | Le Champ "token" est manquant, or il est obligatoire
-2            | Missing Crypted Request                   | Le Champ "crequest" est manquant, or il est obligatoire
-3            | Invalid Token                             | Le Token est invalide, il n'existe pas dans la base Factomos
-4            | Too many API calls for the day            | Vous avez dépassé le nombre maximum d'appels API pour la journée (par défaut limité à 500)
-5            | Invalid Crypted Request                   | La requête n'a pas pu être décryptée, le champ crequest est invalide
-6            | Action not found or invalid               | Le Champ "action" est manquant, or il est obligatoire
-11           | Missing parameter category                | Le Champ "category" est manquant, or il est obligatoire
-12           | Missing parameter client_company_name     | Le Champ "client_company_name" est manquant, or il est obligatoire

## Exemple

POST REQUEST
- action=createClient
- category=client
- client_company_name=name from API
- client_reference=reference from API
- client_contact_lastname=greg
- client_email=g@tfs.im	
- client_phonenumber=012345789
- client_address=address from API
- client_address_more=address more from API
- client_zipcode=zipcode from API	
- client_city=city from API
- client_country=country from API
- client_lang=fr
- number_siret=0123456
- number_tva=987654
- client_website=http://tfs.im
- client_keyword=agency
- description=description from API

JSON RESULT
```json
{
    "client_id":"5782",
    "error":{
        "code":0,
        "message":"OK"
    }
}
```

# 10. Récupérer les infos d'un service à partir de son id

## Paramètres en entrée

- action=getService, (OBLIGATOIRE)
- service_id=<id_du_service>, (OBLIGATOIRE)

## Code d'erreurs

Code d'erreur | Message d'erreur                          | Description
------------: | :---------------------------------------- | :----------------
0             |                                           | Pas d'erreur, la requête s'est bien passée
-1            | Missing Token                             | Le Champ "token" est manquant, or il est obligatoire
-2            | Missing Crypted Request                   | Le Champ "crequest" est manquant, or il est obligatoire
-3            | Invalid Token                             | Le Token est invalide, il n'existe pas dans la base Factomos
-4            | Too many API calls for the day            | Vous avez dépassé le nombre maximum d'appels API pour la journée (par défaut limité à 500)
-5            | Invalid Crypted Request                   | La requête n'a pas pu être décryptée, le champ crequest est invalide
-6            | Action not found or invalid               | Le Champ "action" est manquant, or il est obligatoire
-13           | Missing parameter service_id              | Le Champ "service_id" est manquant, or il est obligatoire
-14           | Service not found                         | Le service avec cet id n'existe pas dans la base Factomos

## Exemple

POST REQUEST
- action=getService
- service_id=67

JSON RESULT
```json
{
    "service":{
        "service_id":"8",
        "company_id":"1",
        "category":"produits",
        "service_name":"Abonnement au service Pictomos",
        "description":"Abonnement au service Pictomos ",
        "unit":"mois",
        "price":"8.27758979797363",
        "vat":"19.6",
        "service_code_analytic":"706000001",
        "archived":"0"
    },
    "error":{
        "code":0,
        "message":"OK"
    }
}
```
    
# 11. Créer un service

## Paramètres en entrée

- action=createService, (OBLIGATOIRE)
- category
- service_name, (OBLIGATOIRE)
- description
- unit
- price, (OBLIGATOIRE)

## Paramètres en sortie

 - service_id: l'identifiant du service (prestation)

## Code d'erreurs

Code d'erreur | Message d'erreur                                                              | Description
------------: | :---------------------------------------------------------------------------- | :----------------
0             |                                                                               | Pas d'erreur, la requête s'est bien passée
-1            | Missing Token                                                                 | Le Champ "token" est manquant, or il est obligatoire
-2            | Missing Crypted Request                                                       | Le Champ "crequest" est manquant, or il est obligatoire
-3            | Invalid Token                                                                 | Le Token est invalide, il n'existe pas dans la base Factomos
-4            | Too many API calls for the day                                                | Vous avez dépassé le nombre maximum d'appels API pour la journée (par défaut limité à 500)
-5            | Invalid Crypted Request                                                       | La requête n'a pas pu être décryptée, le champ crequest est invalide
-6            | Action not found or invalid                                                   | Le Champ "action" est manquant, or il est obligatoire
-15           | Missing parameter service_name                                                | Le Champ "service_name" est manquant, or il est obligatoire
-16           | Missing parameter price                                                       | Le Champ "price" est manquant, or il est obligatoire
-27           | The category of the service must be one of the following (services, produits) | Le champ "category" doit obligatoirement avoir une des valeurs suivantes : 'produits', ou 'services'

## Exemple

POST REQUEST
- action=createService
- category=my category
- service_name=my service
- description=my description
- unit=jour
- price=150

JSON RESULT
```json
{
    "service_id":"5782",
    "error":{
        "code":0,
        "message":"OK"
    }
}
```

# 12. Créer une facture

## Paramètres en entrée

- action=createInvoice, (OBLIGATOIRE)
- client_id, (OBLIGATOIRE)
- invoice_attention
- objet
- invoice_date
- invoice_vat
- payment_condition_enable
- payment_amount
- payment_term
- invoice_comment
- line_service_id[] (value 75 for TITLES), (OBLIGATOIRE)
- line_description[],
- line_quantity[], (OBLIGATOIRE)
- line_price[],
- invoice_comment (optional)
- figures_total_withvat (To force the vat and total with vat calculation on a specific value), it's optional
- invoice_paid (0: No, 1: Yes)
- template_id


## Paramètres en sortie

- invoice_id, l'identifiant de la facture,
- invoice_formated_number, le numéro de facture (chrono),
- invoice_document_key, la clé vous permettant de construire le lien vers la facture, en rajoutant devant https://app.factomos.com/ (exemple de lien : https://app.factomos.com/i5u5pHLyBI4pizucVdo6)
- invoice_date, date de la facture au format YYYY-MM-DD
- client_id, l'identifiant du client

## Code d'erreurs

Code d'erreur | Message d'erreur                          | Description
------------: | :---------------------------------------- | :----------------
0             |                                           | Pas d'erreur, la requête s'est bien passée
-1            | Missing Token                             | Le Champ "token" est manquant, or il est obligatoire
-2            | Missing Crypted Request                   | Le Champ "crequest" est manquant, or il est obligatoire
-3            | Invalid Token                             | Le Token est invalide, il n'existe pas dans la base Factomos
-4            | Too many API calls for the day            | Vous avez dépassé le nombre maximum d'appels API pour la journée (par défaut limité à 500)
-5            | Invalid Crypted Request                   | La requête n'a pas pu être décryptée, le champ crequest est invalide
-6            | Action not found or invalid               | Le Champ "action" est manquant, or il est obligatoire
-9            | Missing parameter client_id               | Le Champ "client_id" est manquant, or il est obligatoire
-10           | Client not found                          | Le client avec cet id n'existe pas dans la base Factomos
-14           | Service not found                         | Le service avec cet id n'existe pas dans la base Factomos


## Exemple

POST REQUEST
- action=createInvoice
- client_id=19
- invoice_date=2012-05-01
- invoice_attention=Monsieur Test
- objet=Facture abonnement
- invoice_vat=19.6
- payment_condition_enable=1
- payment_amount=100
- payment_term=2012-06-01
- line_service_id[]=19
- line_description[]=Abonnement Pictomos
- line_quantity[]=12
- line_price[]=9.5

JSON RESULT
```json
{
    "invoice_id":"5782",
    "invoice_formated_number":"F1465",
    "invoice_document_key":"i5u5pHLyBI4pizucVdo6",
    "invoice_date":"2012-05-01",
    "client_id":"19",
    "error":{
        "code":0,
        "message":"OK"
    }
}
```

Lien vers la facture : https://app.factomos.com/i5u5pHLyBI4pizucVdo6

# 13. Récupérer une facture à partir de son id

## Paramètres en entrée

- action=getInvoice, (OBLIGATOIRE)
- invoice_id=<invoice_id>, (OBLIGATOIRE)

## Paramètres en sortie

- Tout l'objet "invoice" qui correspond à la facture
- à l'intérieur de cet objet il y a le champ "document_key" qui correspond à la clé vous permettant de construire le lien vers la facture, en rajoutant devant https://app.factomos.com/ (exemple de lien : https://app.factomos.com/i5u5pHLyBI4pizucVdo6)

## Code d'erreurs

Code d'erreur | Message d'erreur                          | Description
------------: | :---------------------------------------- | :----------------
0             |                                           | Pas d'erreur, la requête s'est bien passée
-1            | Missing Token                             | Le Champ "token" est manquant, or il est obligatoire
-2            | Missing Crypted Request                   | Le Champ "crequest" est manquant, or il est obligatoire
-3            | Invalid Token                             | Le Token est invalide, il n'existe pas dans la base Factomos
-4            | Too many API calls for the day            | Vous avez dépassé le nombre maximum d'appels API pour la journée (par défaut limité à 500)
-5            | Invalid Crypted Request                   | La requête n'a pas pu être décryptée, le champ crequest est invalide
-6            | Action not found or invalid               | Le Champ "action" est manquant, or il est obligatoire
-17           | Missing parameter invoice_id              | Le Champ "invoice_id" est manquant, or il est obligatoire
-20           | Invoice not found                         | La facture avec cet id n'existe pas dans la base Factomos

## Exemple

POST REQUEST
- action=getInvoice
- invoice_id=67

JSON RESULT
```json
{
    "invoice":{
        "invoice_formated_number":"F09100013",
        "articleList":[
            {
                "service_id":"4017",
                "company_id":"550",
                "category":"services",
                "service_name":"Prise de son",
                "description":"- Mixette\r\n- Micro\r\n- Perche + bonette",
                "unit":"jour",
                "price":"950",
                "supplier_price":"0",
                "is_with_vat":"0",
                "vat":"5.5",
                "service_code_analytic":"706000003",
                "archived":"0",
                "invoice_id":"6829",
                "quantity":"10",
                "remise":"0",
                "invoice_price":"950",
                "comment":"- Mixette\r\n- Micro\r\n- Perche + bonette",
                "unitDiscount":"euro",
                "invoice_order":"0",
                "percent":"0",
                "total_ht":"9500"
            },
            {
                "service_id":"4017",
                "company_id":"550",
                "category":"services",
                "service_name":"Prise de son",
                "description":"- Mixette\r\n- Micro\r\n- Perche + bonette",
                "unit":"jour",
                "price":"950",
                "supplier_price":"0",
                "is_with_vat":"0",
                "vat":"5.5",
                "service_code_analytic":"706000003",
                "archived":"0",
                "invoice_id":"6829",
                "quantity":"12",
                "remise":"0",
                "invoice_price":"950",
                "comment":"- Mixette\r\n- Micro\r\n- Perche + bonette",
                "unitDiscount":"euro",
                "invoice_order":"1",
                "percent":"0",
                "total_ht":"11400"
            },
            {
                "service_id":"4024",
                "company_id":"550",
                "category":"services",
                "service_name":"Techniciens",
                "description":"- Production\r\n- R\u00e9alisation\r\n- Image\r\n- Son\r\n- Montage\r\n- R\u00e9gie\r\n- D\u00e9coration costume, maquillage",
                "unit":"semaine",
                "price":"1300",
                "supplier_price":"0",
                "is_with_vat":"0",
                "vat":"5.5",
                "service_code_analytic":"706000010",
                "archived":"0",
                "invoice_id":"6829",
                "quantity":"4",
                "remise":"0",
                "invoice_price":"1300",
                "comment":"- Production\r\n- R\u00e9alisation\r\n- Image\r\n- Son\r\n- Montage\r\n- R\u00e9gie\r\n- D\u00e9coration costume, maquillage",
                "unitDiscount":"euro",
                "invoice_order":"2",
                "percent":"0",
                "total_ht":"5200"
            }
        ],
        "client_id":"546",
        "invoice_status":"paid",
        "document_key":"ib2cVKO9u4svZxfLS790",
        "document_title":"Facture",
        "estimate_id":"0",
        "exists":true,
        "payment_term_fr":"00\/00\/00",
        "id":"6829",
        "client_company_name":"Extracrea",
        "unix_timestamp":"1284242400",
        "invoice_type":"invoice",
        "invoice_formated_year":"2010",
        "invoice_paid_mention":"0",
        "figures_total_withoutvat":"26100",
        "figures_total_vat":"5115.6",
        "figures_total_withvat":"31215.6",
        "figures_paid":"31215.6",
        "figures_due":"0",
        "invoice_format":"F%m%y%04d"
    },
    "error":{
        "code":0,
        "message":"OK"
    }
}
```

# 14. Editer une facture

## Paramètres en entrée

- action=editInvoice, (OBLIGATOIRE)
- invoice_id, (OBLIGATOIRE)
- client_id, (OBLIGATOIRE)
- invoice_date
- invoice_attention
- objet
- invoice_vat
- payment_condition_enable
- payment_amount
- payment_term
- invoice_comment
- line_service_id[] (value 75 for TITLES), (OBLIGATOIRE)
- line_description[]
- line_quantity[], (OBLIGATOIRE)
- line_price[]
- invoice_comment
- figures_total_withvat (To force the vat and total with vat calculation on a specific value), it's optional
- invoice_paid (0: No, 1: Yes)

## Paramètres en sortie

- invoice_id, l'identifiant de la facture,
- invoice_formated_number, le numéro de facture (chrono),
- invoice_document_key, la clé vous permettant de construire le lien vers la facture (https://app.factomos.com/<invoice_document_key>)

## Code d'erreurs

Code d'erreur | Message d'erreur                          | Description
------------: | :---------------------------------------- | :----------------
0             |                                           | Pas d'erreur, la requête s'est bien passée
-1            | Missing Token                             | Le Champ "token" est manquant, or il est obligatoire
-2            | Missing Crypted Request                   | Le Champ "crequest" est manquant, or il est obligatoire
-3            | Invalid Token                             | Le Token est invalide, il n'existe pas dans la base Factomos
-4            | Too many API calls for the day            | Vous avez dépassé le nombre maximum d'appels API pour la journée (par défaut limité à 500)
-5            | Invalid Crypted Request                   | La requête n'a pas pu être décryptée, le champ crequest est invalide
-6            | Action not found or invalid               | Le Champ "action" est manquant, or il est obligatoire
-9            | Missing parameter client_id               | Le Champ "client_id" est manquant, or il est obligatoire
-10           | Client not found                          | Le client avec cet id n'existe pas dans la base Factomos
-14           | Service not found                         | Le service avec cet id n'existe pas dans la base Factomos
-17           | Missing parameter invoice_id              | Le Champ "invoice_id" est manquant, or il est obligatoire
-20           | Invoice not found                         | La facture avec cet id n'existe pas dans la base Factomos

## Exemple

POST REQUEST
- action=editInvoice
- invoice_id=4624
- client_id=19
- invoice_date=2012-05-01
- invoice_attention=Monsieur Test
- objet=Facture abonnement
- invoice_vat=19.6
- payment_condition_enable=1
- payment_amount=100
- payment_term=2012-06-01
- line_service_id[]=19
- line_description[]=Abonnement Pictomos
- line_quantity[]=12
- line_price[]=9.5

JSON RESULT
```json
{
    "invoice_id":"5782",
    "invoice_formated_number":"5782",
    "invoice_document_key":"5782",
    "error":{
        "code":0,
        "message":"OK"
    }
}
```

# 15. Envoyer une facture

## Paramètres en entrée

- action=sendInvoice, (OBLIGATOIRE)
- invoice_id, (OBLIGATOIRE)
- email_template_id
- email_to

## Code d'erreurs

Code d'erreur | Message d'erreur                          | Description
------------: | :---------------------------------------- | :----------------
0             |                                           | Pas d'erreur, la requête s'est bien passée
-1            | Missing Token                             | Le Champ "token" est manquant, or il est obligatoire
-2            | Missing Crypted Request                   | Le Champ "crequest" est manquant, or il est obligatoire
-3            | Invalid Token                             | Le Token est invalide, il n'existe pas dans la base Factomos
-4            | Too many API calls for the day            | Vous avez dépassé le nombre maximum d'appels API pour la journée (par défaut limité à 500)
-5            | Invalid Crypted Request                   | La requête n'a pas pu être décryptée, le champ crequest est invalide
-6            | Action not found or invalid               | Le Champ "action" est manquant, or il est obligatoire
-17           | Missing parameter invoice_id              | Le Champ "invoice_id" est manquant, or il est obligatoire
-20           | Invoice not found                         | La facture avec cet id n'existe pas dans la base Factomos

## Exemple

POST REQUEST
- action=sendInvoice
- invoice_id=my invoice id
- email_template_id=my email template id
- email_to=my client email

JSON RESULT
```json
{
    "error":{
        "code":0,
        "message":"OK"
    }
}
```

# 16. Créer un règlement

## Paramètres en entrée

- action=createPayment, (OBLIGATOIRE)
- invoice_id, (OBLIGATOIRE)
- montant, (OBLIGATOIRE)
- type (cb, check, especes, paypal, virement, other), (OBLIGATOIRE)
- reference
- payment_date
- numero_remise
- numero_cheque

# 17. Editer un règlement

## Paramètres en entrée

- action=editPayment, (OBLIGATOIRE)
- payment_id (optional it the invoice has only one payment)
- invoice_id, (OBLIGATOIRE)
- montant
- type (cb, check, especes, paypal, virement, other)
- reference
- payment_date
- numero_remise
- numero_cheque

## Code d'erreurs

Code d'erreur | Message d'erreur                                                            | Description
------------: | :-------------------------------------------------------------------------- | :----------------
0             |                                                                             | Pas d'erreur, la requête s'est bien passée
-1            | Missing Token                                                               | Le Champ "token" est manquant, or il est obligatoire
-2            | Missing Crypted Request                                                     | Le Champ "crequest" est manquant, or il est obligatoire
-3            | Invalid Token                                                               | Le Token est invalide, il n'existe pas dans la base Factomos
-4            | Too many API calls for the day                                              | Vous avez dépassé le nombre maximum d'appels API pour la journée (par défaut limité à 500)
-5            | Invalid Crypted Request                                                     | La requête n'a pas pu être décryptée, le champ crequest est invalide
-6            | Action not found or invalid                                                 | Le Champ "action" est manquant, or il est obligatoire
-17           | Missing parameter invoice_id                                                | Le Champ "invoice_id" est manquant, or il est obligatoire
-20           | Invoice not found                                                           | La facture avec cet id n'existe pas dans la base Factomos
-21           | Several payments for this invoice, you have to add the parameter payment_id | Vous devez ajouter le champ "payment_id" car il y a plusieurs règlements pour cette facture

# 18. Supprimer tous les règlements d'une facture

## Paramètres en entrée

- action=deleteAllPayments, (OBLIGATOIRE)
- invoice_id, (OBLIGATOIRE)


## Code d'erreurs

Code d'erreur | Message d'erreur                          | Description
------------: | :---------------------------------------- | :----------------
0             |                                           | Pas d'erreur, la requête s'est bien passée
-1            | Missing Token                             | Le Champ "token" est manquant, or il est obligatoire
-2            | Missing Crypted Request                   | Le Champ "crequest" est manquant, or il est obligatoire
-3            | Invalid Token                             | Le Token est invalide, il n'existe pas dans la base Factomos
-4            | Too many API calls for the day            | Vous avez dépassé le nombre maximum d'appels API pour la journée (par défaut limité à 500)
-5            | Invalid Crypted Request                   | La requête n'a pas pu être décryptée, le champ crequest est invalide
-6            | Action not found or invalid               | Le Champ "action" est manquant, or il est obligatoire
-17           | Missing parameter invoice_id              | Le Champ "invoice_id" est manquant, or il est obligatoire
-20           | Invoice not found                         | La facture avec cet id n'existe pas dans la base Factomos

# 19. Créer une dépense

## Paramètres en entrée

- action=createExpense, (OBLIGATOIRE)
- date
- supplier_id, (OBLIGATOIRE)
- invoice_number
- vat
- amount, (OBLIGATOIRE)
- payment_type (cb, check, virement, prelevement, especes, tip, other), (OBLIGATOIRE)
- payment_reference
- comment

## Paramètres en sortie

- expense_id: l'identifiant de la dépense

## Code d'erreurs

Code d'erreur | Message d'erreur                          | Description
------------: | :---------------------------------------- | :----------------
0             |                                           | Pas d'erreur, la requête s'est bien passée
-1            | Missing Token                             | Le Champ "token" est manquant, or il est obligatoire
-2            | Missing Crypted Request                   | Le Champ "crequest" est manquant, or il est obligatoire
-3            | Invalid Token                             | Le Token est invalide, il n'existe pas dans la base Factomos
-4            | Too many API calls for the day            | Vous avez dépassé le nombre maximum d'appels API pour la journée (par défaut limité à 500)
-5            | Invalid Crypted Request                   | La requête n'a pas pu être décryptée, le champ crequest est invalide
-6            | Action not found or invalid               | Le Champ "action" est manquant, or il est obligatoire
-18           | Missing parameter amount                  | Le Champ "amount" est manquant, or il est obligatoire
-19           | Missing parameter payment_type            | Le Champ "payment_type" est manquant, or il est obligatoire
-21           | Missing parameter supplier_id             | Le Champ "supplier_id" est manquant, or il est obligatoire

## Exemple

POST REQUEST
- action=createExpense
- date=2012-12-10
- supplier_id
- invoice_number
- vat
- amount
- payment_type (cb, check, virement, prelevement, especes, tip, other)
- payment_reference
- comment

JSON RESULT
```json
{
    "expense_id":"5782",
    "error":{
        "code":0,
        "message":"OK"
    }
}
```

# 20. Editer une dépense

## Paramètres en entrée

- action=editExpense, (OBLIGATOIRE)
- expense_id, (OBLIGATOIRE)
- date
- supplier_id, (OBLIGATOIRE)
- invoice_number
- vat
- amount, (OBLIGATOIRE)
- payment_type (cb, check, virement, prelevement, especes, tip, other), (OBLIGATOIRE)
- payment_reference
- comment

## Paramètres en sortie

- expense_id: l'identifiant de la dépense

## Code d'erreurs

Code d'erreur | Message d'erreur                          | Description
------------: | :---------------------------------------- | :----------------
0             |                                           | Pas d'erreur, la requête s'est bien passée
-1            | Missing Token                             | Le Champ "token" est manquant, or il est obligatoire
-2            | Missing Crypted Request                   | Le Champ "crequest" est manquant, or il est obligatoire
-3            | Invalid Token                             | Le Token est invalide, il n'existe pas dans la base Factomos
-4            | Too many API calls for the day            | Vous avez dépassé le nombre maximum d'appels API pour la journée (par défaut limité à 500)
-5            | Invalid Crypted Request                   | La requête n'a pas pu être décryptée, le champ crequest est invalide
-6            | Action not found or invalid               | Le Champ "action" est manquant, or il est obligatoire
-18           | Missing parameter amount                  | Le Champ "amount" est manquant, or il est obligatoire
-19           | Missing parameter payment_type            | Le Champ "payment_type" est manquant, or il est obligatoire
-21           | Missing parameter supplier_id             | Le Champ "supplier_id" est manquant, or il est obligatoire
-23           | Missing parameter expense_id              | Le Champ "expense_id" est manquant, or il est obligatoire
-24           | Expense not found                         | La dépense avec cet id n'existe pas dans la base Factomos


## Exemple

POST REQUEST
- action=editExpense
- expense_id=5
- date=2012-12-10
- supplier_id
- invoice_number
- vat
- amount
- payment_type (cb, check, virement, prelevement, especes, tip, other)
- payment_reference
- comment

JSON RESULT
```json
{
    "expense_id":"5782",
    "error":{
        "code":0,
        "message":"OK"
    }
}
```


# 21. Récupérer une dépense

## Paramètres en entrée

- action=getExpense, (OBLIGATOIRE)
- expense_id, (OBLIGATOIRE)

## Code d'erreurs

Code d'erreur | Message d'erreur                          | Description
------------: | :---------------------------------------- | :----------------
0             |                                           | Pas d'erreur, la requête s'est bien passée
-1            | Missing Token                             | Le Champ "token" est manquant, or il est obligatoire
-2            | Missing Crypted Request                   | Le Champ "crequest" est manquant, or il est obligatoire
-3            | Invalid Token                             | Le Token est invalide, il n'existe pas dans la base Factomos
-4            | Too many API calls for the day            | Vous avez dépassé le nombre maximum d'appels API pour la journée (par défaut limité à 500)
-5            | Invalid Crypted Request                   | La requête n'a pas pu être décryptée, le champ crequest est invalide
-6            | Action not found or invalid               | Le Champ "action" est manquant, or il est obligatoire
-23           | Missing parameter expense_id              | Le Champ "expense_id" est manquant, or il est obligatoire
-24           | Expense not found                         | La dépense avec cet id n'existe pas dans la base Factomos


## Exemple

POST REQUEST
- action=editExpense
- expense_id=5

JSON RESULT
```json
{
    "expense":{
        "exists":true,
        "expense_id":"25245",
        "date":"2014-02-25",
        "supplier_id":"15728",
        "invoice_number":"IN-81450-2",
        "vat":"0",
        "amount":"3.66",
        "payment_type":"check",
        "payment_reference":"ref",
        "comment":"CARTES DE VISITE 2"
    },
    "error":{
        "code":0,
        "message":"OK"
    }
}
```

# 22. Récupérer un devis à partir de son id

## Paramètres en entrée

- action=getEstimate, (OBLIGATOIRE)
- estimate_id=<estimate_id>, (OBLIGATOIRE)

## Code d'erreurs

Code d'erreur | Message d'erreur                          | Description
------------: | :---------------------------------------- | :----------------
0             |                                           | Pas d'erreur, la requête s'est bien passée
-1            | Missing Token                             | Le Champ "token" est manquant, or il est obligatoire
-2            | Missing Crypted Request                   | Le Champ "crequest" est manquant, or il est obligatoire
-3            | Invalid Token                             | Le Token est invalide, il n'existe pas dans la base Factomos
-4            | Too many API calls for the day            | Vous avez dépassé le nombre maximum d'appels API pour la journée (par défaut limité à 500)
-5            | Invalid Crypted Request                   | La requête n'a pas pu être décryptée, le champ crequest est invalide
-6            | Action not found or invalid               | Le Champ "action" est manquant, or il est obligatoire
-25           | Missing parameter estimate_id             | Le Champ "estimate_id" est manquant, or il est obligatoire
-26           | Estimate not found                        | Le devis avec cet id n'existe pas dans la base Factomos

## Exemple

POST REQUEST
- action=getEstimate
- estimate_id=67

JSON RESULT
```json
{
    "estimate":{
        "estimate_formated_number":"D14050050",
        "articleList":[
            {
            "service_id":"4023",
            "company_id":"550",
            "category":"services",
            "service_name":"D\u00e9cors, d\u00e9guisements",
            "description":"- Meubles\r\n- Maquillage\r\n- Costumes",
            "unit":"forfait",
            "price":"650",
            "supplier_price":"0",
            "is_with_vat":"0",
            "vat":"5.5",
            "service_code_analytic":"706000009",
            "archived":"0",
            "stock":"0",
            "image":"",
            "reference":"",
            "estimate_id":"19709",
            "quantity":"1",
            "remise":"0",
            "estimate_price":"650",
            "comment":"- Meubles\r\n- Maquillage\r\n- Costumes",
            "estimate_order":"0",
            "id":"19709",
            "template_id":"1247",
            "estimate_number":"50",
            "estimate_formated_number":"D14050050",
            "client_id":"8827",
            "client_company_name":"C.E.R.T.O.S",
            "estimate_status":"pending",
            "estimate_vat":"0",
            "document_title":"Devis",
            "several_vat":"1",
            "company_vat":"20",
            "tva_enable":"1",
            "estimate_date":"2014-05-13",
            "estimate_action_date":null,
            "estimate_attention":"David Ravalet",
            "objet":"",
            "estimate_comment":"",
            "estimate_sentmail":"1",
            "with_precompte":"0",
            "figures_total_withoutvat":"650",
            "figures_total_vat":"35.75",
            "figures_total_withvat":"685.75",
            "document_lang":"fr",
            "date_update":"0000-00-00 00:00:00",
            "percent":"0",
            "total_ht":"650"
            }
        ],
        "estimate_status":"pending",
        "document_title":"Devis",
        "document_key":"e2e8CSzakvh4DecbG9k5",
        "exists":true,
        "id":"19709",
        "estimate_number":"50",
        "unix_timestamp":"1399932000",
        "figures_total_withoutvat":"650",
        "figures_total_vat":"35.75",
        "figures_total_withvat":"685.75",
        "estimate_format":"D%y%m%04d"
    },
    "error":{
        "code":0,
        "message":"OK"
    }
}
```

# 23. Créer un devis

## Paramètres en entrée

- action=createEstimate, (OBLIGATOIRE)
- client_id, (OBLIGATOIRE)
- estimate_attention
- objet
- estimate_vat
- line_service_id[] (value 75 for TITLES), (OBLIGATOIRE)
- line_description[],
- line_quantity[], (OBLIGATOIRE)
- line_price[],
- estimate_comment (optional)
- figures_total_withvat (To force the vat and total with vat calculation on a specific value), it's optional
- template_id


## Paramètres en sortie

- estimate_id, l'identifiant du devis,
- estimate_formated_number, le numéro de devis (chrono),
- estimate_document_key, la clé vous permettant de construire le lien vers le devis, en rajoutant devant https://app.factomos.com/ (exemple de lien : https://app.factomos.com/e5u5pHLyBI4pizucVdo6)
- estimate_date, date du devis au format YYYY-MM-DD
- client_id, l'identifiant du client

## Code d'erreurs

Code d'erreur | Message d'erreur                          | Description
------------: | :---------------------------------------- | :----------------
0             |                                           | Pas d'erreur, la requête s'est bien passée
-1            | Missing Token                             | Le Champ "token" est manquant, or il est obligatoire
-2            | Missing Crypted Request                   | Le Champ "crequest" est manquant, or il est obligatoire
-3            | Invalid Token                             | Le Token est invalide, il n'existe pas dans la base Factomos
-4            | Too many API calls for the day            | Vous avez dépassé le nombre maximum d'appels API pour la journée (par défaut limité à 500)
-5            | Invalid Crypted Request                   | La requête n'a pas pu être décryptée, le champ crequest est invalide
-6            | Action not found or invalid               | Le Champ "action" est manquant, or il est obligatoire
-9            | Missing parameter client_id               | Le Champ "client_id" est manquant, or il est obligatoire
-10           | Client not found                          | Le client avec cet id n'existe pas dans la base Factomos
-14           | Service not found                         | Le service avec cet id n'existe pas dans la base Factomos


## Exemple

POST REQUEST
- action=createEstimate
- client_id=19
- estimate_date=2012-05-01
- estimate_attention=Monsieur Test
- objet=Site Artiste
- estimate_vat=19.6
- line_service_id[]=19
- line_description[]=Abonnement Pictomos
- line_quantity[]=12
- line_price[]=9.5

JSON RESULT
```json
{
    "estimate_id":"5782",
    "estimate_formated_number":"F1465",
    "estimate_document_key":"e5u5pHLyBI4pizucVdo6",
    "estimate_date":"2012-05-01",
    "client_id":"19",
    "error":{
        "code":0,
        "message":"OK"
    }
}
```

Lien vers le devis : https://app.factomos.com/e5u5pHLyBI4pizucVdo6

# 24. Créer une facture à partir d'un devis

## Paramètres en entrée

- action=transformEstimate, (OBLIGATOIRE)
- estimate_id=<estimate_id>, (OBLIGATOIRE)

## Code d'erreurs

Code d'erreur | Message d'erreur                          | Description
------------: | :---------------------------------------- | :----------------
0             |                                           | Pas d'erreur, la requête s'est bien passée
-1            | Missing Token                             | Le Champ "token" est manquant, or il est obligatoire
-2            | Missing Crypted Request                   | Le Champ "crequest" est manquant, or il est obligatoire
-3            | Invalid Token                             | Le Token est invalide, il n'existe pas dans la base Factomos
-4            | Too many API calls for the day            | Vous avez dépassé le nombre maximum d'appels API pour la journée (par défaut limité à 500)
-5            | Invalid Crypted Request                   | La requête n'a pas pu être décryptée, le champ crequest est invalide
-6            | Action not found or invalid               | Le Champ "action" est manquant, or il est obligatoire
-25           | Missing parameter estimate_id             | Le Champ "estimate_id" est manquant, or il est obligatoire
-26           | Estimate not found                        | Le devis avec cet id n'existe pas dans la base Factomos

## Exemple

POST REQUEST
- action=transformEstimate
- estimate_id=67

JSON RESULT
```json
{
    "invoice_id":"5782",
    "invoice_formated_number":"F1465",
    "invoice_document_key":"i5u5pHLyBI4pizucVdo6",
    "invoice_date":"2012-05-01",
    "client_id":"19",
    "error":{
        "code":0,
        "message":"OK"
    }
}
```
