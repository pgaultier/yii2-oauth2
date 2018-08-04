create table oauthClients
(
  id          varchar(255) not null
    primary key,
  secret      varchar(255) null,
  isPublic    tinyint(1)   null,
  redirectUri varchar(255) null,
  userId      varchar(255) null,
  name        varchar(255) null,
  dateCreated datetime     null,
  dateUpdated datetime     null,
  dateDeleted datetime     null
);

create table oauthAccessTokens
(
  id          varchar(255) not null
    primary key,
  clientId    varchar(255) null,
  userId      varchar(255) null,
  expiry      datetime     null,
  dateCreated datetime     null,
  dateUpdated datetime     null,
  dateDeleted datetime     null,
  constraint accessTokens_clients_id_fk
  foreign key (clientId) references oauthClients (id)
    on update cascade
    on delete set null
);

create index accessTokens_clients_id_fk
  on oauthAccessTokens (clientId);

create table oauthAuthorizationCodes
(
  id          varchar(255) not null
    primary key,
  clientId    varchar(255) null,
  userId      varchar(255) null,
  redirectUri varchar(255) null,
  expiry      datetime     null,
  tokenId     varchar(255) null,
  dateCreated datetime     null,
  dateUpdated datetime     null,
  dateDeleted datetime     null,
  constraint authorizationCodes_clients_id_fk
  foreign key (clientId) references oauthClients (id)
    on update cascade
    on delete set null,
  constraint authorizationCodes_accessTokens_id_fk
  foreign key (tokenId) references oauthAccessTokens (id)
    on update cascade
    on delete set null
);

create index authorizationCodes_accessTokens_id_fk
  on oauthAuthorizationCodes (tokenId);

create index authorizationCodes_clients_id_fk
  on oauthAuthorizationCodes (clientId);

create table oauthClientUser
(
  clientId varchar(255) not null,
  userId   varchar(255) not null,
  primary key (clientId, userId),
  constraint client_user_client_id_fk
  foreign key (clientId) references oauthClients (id)
    on update cascade
    on delete cascade
);

create table oauthCypherKeys
(
  id                  varchar(255) not null
    primary key,
  publicKey           text         null,
  privateKey          text         null,
  encryptionAlgorithm varchar(255) null,
  dateCreated         datetime     null,
  dateUpdated         datetime     null,
  dateDeleted         datetime     null
);

create table oauthGrantTypes
(
  id          varchar(255) not null
    primary key,
  dateCreated datetime     null,
  dateUpdated datetime     null,
  dateDeleted datetime     null
);

insert into oauthGrantTypes (id, dateCreated, dateUpdated, dateDeleted)
values ('authorization_code', '2018-06-26 13:25:41', '2018-06-26 13:25:41', null);

insert into oauthGrantTypes (id, dateCreated, dateUpdated, dateDeleted)
values ('client_credentials', '2018-06-26 13:25:41', '2018-06-26 13:25:41', null);

insert into oauthGrantTypes (id, dateCreated, dateUpdated, dateDeleted)
values ('password', '2018-06-26 13:25:41', '2018-06-26 13:25:41', null);

insert into oauthGrantTypes (id, dateCreated, dateUpdated, dateDeleted)
values ('refresh_token', '2018-06-26 13:25:41', '2018-06-26 13:25:41', null);

create table oauthClientGrantType
(
  clientId    varchar(255) not null,
  grantTypeId varchar(255) not null,
  primary key (clientId, grantTypeId),
  constraint client_grantType_client_id_fk
  foreign key (clientId) references oauthClients (id)
    on update cascade
    on delete cascade,
  constraint client_grantType_grantType_id_fk
  foreign key (grantTypeId) references oauthGrantTypes (id)
    on update cascade
    on delete cascade
);

create index client_grantType_grantType_id_fk
  on oauthClientGrantType (grantTypeId);

create table oauthJtis
(
  id          varchar(255) not null
    primary key,
  clientId    varchar(255) null,
  subject     varchar(255) null,
  audience    varchar(255) null,
  expires     datetime     null,
  jti         varchar(255) null,
  dateCreated datetime     null,
  dateUpdated datetime     null,
  dateDeleted datetime     null,
  constraint jtis_clients_id_fk
  foreign key (clientId) references oauthClients (id)
    on update cascade
    on delete set null
);

create index jtis_clients_id_fk
  on oauthJtis (clientId);

create table oauthJwts
(
  id          varchar(255) not null
    primary key,
  clientId    varchar(255) null,
  subject     varchar(255) null,
  publicKey   varchar(255) null,
  dateCreated datetime     null,
  dateUpdated datetime     null,
  dateDeleted datetime     null,
  constraint jwts_clients_id_fk
  foreign key (clientId) references oauthClients (id)
    on update cascade
    on delete set null
);

create index jwts_clients_id_fk
  on oauthJwts (clientId);

create table oauthRefreshTokens
(
  id          varchar(255) not null
    primary key,
  clientId    varchar(255) null,
  userId      varchar(255) null,
  expiry      datetime     null,
  dateCreated datetime     null,
  dateUpdated datetime     null,
  dateDeleted datetime     null,
  constraint refreshTokens_clients_id_fk
  foreign key (clientId) references oauthClients (id)
    on update cascade
    on delete set null
);

create index refreshTokens_clients_id_fk
  on oauthRefreshTokens (clientId);

create table oauthScopes
(
  id          varchar(255) not null
    primary key,
  definition  varchar(255) null,
  isDefault   tinyint(1)   null,
  dateCreated datetime     null,
  dateUpdated datetime     null,
  dateDeleted datetime     null
);

create table oauthScopeAccessToken
(
  scopeId       varchar(255) not null,
  accessTokenId varchar(255) not null,
  primary key (scopeId, accessTokenId),
  constraint scope_accessToken_scopes_id_fk
  foreign key (scopeId) references oauthScopes (id)
    on update cascade
    on delete cascade,
  constraint scope_accessToken_accessTokens_id_fk
  foreign key (accessTokenId) references oauthAccessTokens (id)
    on update cascade
    on delete cascade
);

create index scope_accessToken_accessTokens_id_fk
  on oauthScopeAccessToken (accessTokenId);

create table oauthScopeAuthorizationCode
(
  scopeId             varchar(255) not null,
  authorizationCodeId varchar(255) not null,
  primary key (scopeId, authorizationCodeId),
  constraint scope_authorizationCode_scopes_id_fk
  foreign key (scopeId) references oauthScopes (id)
    on update cascade
    on delete cascade,
  constraint scope_authorizationCode_authorizationCodes_id_fk
  foreign key (authorizationCodeId) references oauthAuthorizationCodes (id)
    on update cascade
    on delete cascade
);

create index scope_authorizationCode_authorizationCodes_id_fk
  on oauthScopeAuthorizationCode (authorizationCodeId);

create table oauthScopeClient
(
  scopeId  varchar(255) not null,
  clientId varchar(255) not null,
  primary key (scopeId, clientId),
  constraint scope_client_scopes_id_fk
  foreign key (scopeId) references oauthScopes (id)
    on update cascade
    on delete cascade,
  constraint scope_client_clients_id_fk
  foreign key (clientId) references oauthClients (id)
    on update cascade
    on delete cascade
);

create index scope_client_clients_id_fk
  on oauthScopeClient (clientId);

create table oauthScopeRefreshToken
(
  scopeId        varchar(255) not null,
  refreshTokenId varchar(255) not null,
  primary key (scopeId, refreshTokenId),
  constraint scope_refreshToken_scopes_id_fk
  foreign key (scopeId) references oauthScopes (id)
    on update cascade
    on delete cascade,
  constraint scope_refreshToken_refreshTokens_id_fk
  foreign key (refreshTokenId) references oauthRefreshTokens (id)
    on update cascade
    on delete cascade
);

create index scope_refreshToken_refreshTokens_id_fk
  on oauthScopeRefreshToken (refreshTokenId);

