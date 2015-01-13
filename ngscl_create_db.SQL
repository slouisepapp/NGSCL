--
-- Run script on PostgreSQL as postgres user.
--

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

--
-- Name: ngscl; Type: DATABASE; Schema: -; Owner: postgres
--

CREATE DATABASE ngscl WITH TEMPLATE = template0 ENCODING = 'UTF8' LC_COLLATE = 'C' LC_CTYPE = 'C';

\connect ngscl

CREATE ROLE ngscl_schema LOGIN
  ENCRYPTED PASSWORD 'change_password'
  NOSUPERUSER INHERIT NOCREATEDB NOCREATEROLE;
CREATE ROLE dac_grants NOLOGIN
  NOSUPERUSER INHERIT NOCREATEDB NOCREATEROLE;
CREATE ROLE pi_user NOLOGIN
  NOSUPERUSER INHERIT NOCREATEDB NOCREATEROLE;
CREATE ROLE lab_user LOGIN
  ENCRYPTED PASSWORD 'change_password'
  NOSUPERUSER INHERIT NOCREATEDB NOCREATEROLE;
GRANT dac_grants TO lab_user;
