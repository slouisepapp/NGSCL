--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET lock_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

--
-- Name: ngscl_schema; Type: SCHEMA; Schema: -; Owner: ngscl_schema
--

CREATE SCHEMA ngscl_schema;


ALTER SCHEMA ngscl_schema OWNER TO ngscl_schema;

SET search_path = ngscl_schema, pg_catalog;

--
-- Name: export_prep_note_image(integer, integer, text); Type: FUNCTION; Schema: ngscl_schema; Owner: postgres
--

CREATE FUNCTION export_prep_note_image(in_library_prep_note_uid integer, in_image_number integer, in_temp_file text) RETURNS boolean
    LANGUAGE plpgsql SECURITY DEFINER
    AS $$
DECLARE
  image_exists integer;
  export_success boolean := FALSE;
BEGIN
  BEGIN
    SELECT COUNT(1) 
      INTO STRICT image_exists
      FROM library_prep_note_image
     WHERE library_prep_note_uid = in_library_prep_note_uid AND
           image_number = in_image_number;
    IF (image_exists > 0) THEN
      PERFORM lo_export(library_prep_note_image, in_temp_file)
         FROM library_prep_note_image
        WHERE library_prep_note_uid = in_library_prep_note_uid AND
              image_number = in_image_number;
      export_success := TRUE;
    END IF; -- (image_exists > 0) THEN
  EXCEPTION
    WHEN others THEN
      RETURN FALSE;
  END;
  RETURN export_success;
END;
$$;


ALTER FUNCTION ngscl_schema.export_prep_note_image(in_library_prep_note_uid integer, in_image_number integer, in_temp_file text) OWNER TO postgres;

--
-- Name: import_prep_note_image(integer, integer, text); Type: FUNCTION; Schema: ngscl_schema; Owner: postgres
--

CREATE FUNCTION import_prep_note_image(in_library_prep_note_uid integer, in_image_number integer, in_uploadfile text) RETURNS boolean
    LANGUAGE plpgsql SECURITY DEFINER
    AS $$
BEGIN
  BEGIN
    INSERT INTO library_prep_note_image
     (library_prep_note_uid, image_number,
      library_prep_note_image)
    VALUES
     (in_library_prep_note_uid, in_image_number,
      lo_import (in_uploadfile));
  EXCEPTION
    WHEN others THEN
      RETURN FALSE;
  END;
  RETURN TRUE;
END;
$$;


ALTER FUNCTION ngscl_schema.import_prep_note_image(in_library_prep_note_uid integer, in_image_number integer, in_uploadfile text) OWNER TO postgres;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: archive; Type: TABLE; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

CREATE TABLE archive (
    archive_uid integer NOT NULL,
    sample_uid integer NOT NULL,
    ref_archive_name_uid integer NOT NULL,
    freezer character varying(100) NOT NULL,
    shelf_number integer NOT NULL,
    box_number character varying(20) NOT NULL,
    box_position character varying(50) NOT NULL,
    comments text
);


ALTER TABLE ngscl_schema.archive OWNER TO ngscl_schema;

--
-- Name: archive_archive_uid_seq; Type: SEQUENCE; Schema: ngscl_schema; Owner: ngscl_schema
--

CREATE SEQUENCE archive_archive_uid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE ngscl_schema.archive_archive_uid_seq OWNER TO ngscl_schema;

--
-- Name: archive_archive_uid_seq; Type: SEQUENCE OWNED BY; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER SEQUENCE archive_archive_uid_seq OWNED BY archive.archive_uid;


--
-- Name: contact; Type: TABLE; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

CREATE TABLE contact (
    contact_uid integer NOT NULL,
    name character varying(50) NOT NULL,
    primary_investigator_uid integer NOT NULL,
    email_address character varying(150),
    phone_number character varying(50),
    comments text
);


ALTER TABLE ngscl_schema.contact OWNER TO ngscl_schema;

--
-- Name: contact_contact_uid_seq; Type: SEQUENCE; Schema: ngscl_schema; Owner: ngscl_schema
--

CREATE SEQUENCE contact_contact_uid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE ngscl_schema.contact_contact_uid_seq OWNER TO ngscl_schema;

--
-- Name: contact_contact_uid_seq; Type: SEQUENCE OWNED BY; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER SEQUENCE contact_contact_uid_seq OWNED BY contact.contact_uid;


--
-- Name: deleted_project_log; Type: TABLE; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

CREATE TABLE deleted_project_log (
    deleted_project_log_uid integer NOT NULL,
    project_uid integer NOT NULL,
    deleted_timestamp timestamp without time zone DEFAULT now() NOT NULL,
    name_of_experimenter text,
    experiment_title text,
    core_director_approval boolean DEFAULT false NOT NULL,
    core_director_approval_date date,
    primary_investigator_approval boolean DEFAULT false NOT NULL,
    primary_investigator_approval_date date,
    run_number integer,
    lanes text,
    run_date date,
    experiment_type text,
    results_summary text,
    estimated_price double precision,
    estimated_price_comments text,
    hypothesis text,
    cells_used text,
    experimental_methods text,
    time_course text,
    experimental_category text,
    conditions_comments text,
    batching_instructions text,
    sample_purification_method text,
    kits_used text,
    amplification_method text,
    barcoding_used text,
    new_method_or_comparison_study text,
    library_prep_comments text,
    read_length text,
    adapter_sequences text,
    barcode_sequences text,
    account_number text
);


ALTER TABLE ngscl_schema.deleted_project_log OWNER TO ngscl_schema;

--
-- Name: deleted_project_log_deleted_project_log_uid_seq; Type: SEQUENCE; Schema: ngscl_schema; Owner: ngscl_schema
--

CREATE SEQUENCE deleted_project_log_deleted_project_log_uid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE ngscl_schema.deleted_project_log_deleted_project_log_uid_seq OWNER TO ngscl_schema;

--
-- Name: deleted_project_log_deleted_project_log_uid_seq; Type: SEQUENCE OWNED BY; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER SEQUENCE deleted_project_log_deleted_project_log_uid_seq OWNED BY deleted_project_log.deleted_project_log_uid;


--
-- Name: deleted_project_log_run_lane; Type: TABLE; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

CREATE TABLE deleted_project_log_run_lane (
    deleted_project_log_run_lane_uid integer NOT NULL,
    deleted_project_log_uid integer NOT NULL,
    sample_name character varying(40),
    sample_description text,
    species character varying(100) NOT NULL,
    batch_group character varying(15)
);


ALTER TABLE ngscl_schema.deleted_project_log_run_lane OWNER TO ngscl_schema;

--
-- Name: deleted_project_log_run_lane_deleted_project_log_run_lane_u_seq; Type: SEQUENCE; Schema: ngscl_schema; Owner: ngscl_schema
--

CREATE SEQUENCE deleted_project_log_run_lane_deleted_project_log_run_lane_u_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE ngscl_schema.deleted_project_log_run_lane_deleted_project_log_run_lane_u_seq OWNER TO ngscl_schema;

--
-- Name: deleted_project_log_run_lane_deleted_project_log_run_lane_u_seq; Type: SEQUENCE OWNED BY; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER SEQUENCE deleted_project_log_run_lane_deleted_project_log_run_lane_u_seq OWNED BY deleted_project_log_run_lane.deleted_project_log_run_lane_uid;


--
-- Name: library_prep_note; Type: TABLE; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

CREATE TABLE library_prep_note (
    library_prep_note_uid integer NOT NULL,
    creation_date date DEFAULT ('now'::text)::date NOT NULL,
    library_prep_note_name character varying(50) NOT NULL,
    comments text
);


ALTER TABLE ngscl_schema.library_prep_note OWNER TO ngscl_schema;

--
-- Name: library_prep_note_image; Type: TABLE; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

CREATE TABLE library_prep_note_image (
    library_prep_note_image_uid integer NOT NULL,
    library_prep_note_uid integer NOT NULL,
    image_number integer NOT NULL,
    library_prep_note_image oid
);


ALTER TABLE ngscl_schema.library_prep_note_image OWNER TO ngscl_schema;

--
-- Name: library_prep_note_image_library_prep_note_image_uid_seq; Type: SEQUENCE; Schema: ngscl_schema; Owner: ngscl_schema
--

CREATE SEQUENCE library_prep_note_image_library_prep_note_image_uid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE ngscl_schema.library_prep_note_image_library_prep_note_image_uid_seq OWNER TO ngscl_schema;

--
-- Name: library_prep_note_image_library_prep_note_image_uid_seq; Type: SEQUENCE OWNED BY; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER SEQUENCE library_prep_note_image_library_prep_note_image_uid_seq OWNED BY library_prep_note_image.library_prep_note_image_uid;


--
-- Name: library_prep_note_library_prep_note_uid_seq; Type: SEQUENCE; Schema: ngscl_schema; Owner: ngscl_schema
--

CREATE SEQUENCE library_prep_note_library_prep_note_uid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE ngscl_schema.library_prep_note_library_prep_note_uid_seq OWNER TO ngscl_schema;

--
-- Name: library_prep_note_library_prep_note_uid_seq; Type: SEQUENCE OWNED BY; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER SEQUENCE library_prep_note_library_prep_note_uid_seq OWNED BY library_prep_note.library_prep_note_uid;


--
-- Name: library_prep_note_sample; Type: TABLE; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

CREATE TABLE library_prep_note_sample (
    library_prep_note_sample_uid integer NOT NULL,
    library_prep_note_uid integer NOT NULL,
    sample_uid integer NOT NULL
);


ALTER TABLE ngscl_schema.library_prep_note_sample OWNER TO ngscl_schema;

--
-- Name: library_prep_note_sample_library_prep_note_sample_uid_seq; Type: SEQUENCE; Schema: ngscl_schema; Owner: ngscl_schema
--

CREATE SEQUENCE library_prep_note_sample_library_prep_note_sample_uid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE ngscl_schema.library_prep_note_sample_library_prep_note_sample_uid_seq OWNER TO ngscl_schema;

--
-- Name: library_prep_note_sample_library_prep_note_sample_uid_seq; Type: SEQUENCE OWNED BY; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER SEQUENCE library_prep_note_sample_library_prep_note_sample_uid_seq OWNED BY library_prep_note_sample.library_prep_note_sample_uid;


--
-- Name: ngscl_role; Type: TABLE; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

CREATE TABLE ngscl_role (
    ngscl_role_uid integer NOT NULL,
    ngscl_role character varying(60) NOT NULL,
    primary_investigator_uid integer
);


ALTER TABLE ngscl_schema.ngscl_role OWNER TO ngscl_schema;

--
-- Name: ngscl_role_ngscl_role_uid_seq; Type: SEQUENCE; Schema: ngscl_schema; Owner: ngscl_schema
--

CREATE SEQUENCE ngscl_role_ngscl_role_uid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE ngscl_schema.ngscl_role_ngscl_role_uid_seq OWNER TO ngscl_schema;

--
-- Name: ngscl_role_ngscl_role_uid_seq; Type: SEQUENCE OWNED BY; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER SEQUENCE ngscl_role_ngscl_role_uid_seq OWNED BY ngscl_role.ngscl_role_uid;


--
-- Name: notification_address; Type: TABLE; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

CREATE TABLE notification_address (
    notification_address_uid integer NOT NULL,
    username character varying(50) NOT NULL,
    domain_name character varying(50) DEFAULT 'scripps.edu'::character varying NOT NULL,
    name character varying(50),
    part_of_to_list boolean DEFAULT true NOT NULL
);


ALTER TABLE ngscl_schema.notification_address OWNER TO ngscl_schema;

--
-- Name: notification_address_notification_address_uid_seq; Type: SEQUENCE; Schema: ngscl_schema; Owner: ngscl_schema
--

CREATE SEQUENCE notification_address_notification_address_uid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE ngscl_schema.notification_address_notification_address_uid_seq OWNER TO ngscl_schema;

--
-- Name: notification_address_notification_address_uid_seq; Type: SEQUENCE OWNED BY; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER SEQUENCE notification_address_notification_address_uid_seq OWNED BY notification_address.notification_address_uid;


--
-- Name: pi16_contact; Type: VIEW; Schema: ngscl_schema; Owner: ngscl_schema
--

CREATE VIEW pi16_contact AS
 SELECT contact.contact_uid,
    contact.name,
    contact.primary_investigator_uid,
    contact.email_address,
    contact.phone_number,
    contact.comments
   FROM contact
  WHERE (contact.primary_investigator_uid = 16);


ALTER TABLE ngscl_schema.pi16_contact OWNER TO ngscl_schema;

--
-- Name: primary_investigator; Type: TABLE; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

CREATE TABLE primary_investigator (
    primary_investigator_uid integer NOT NULL,
    name character varying(50) NOT NULL,
    email_address character varying(150),
    phone_number character varying(50),
    comments text,
    status character varying(50) DEFAULT 'Active'::character varying NOT NULL,
    CONSTRAINT primary_investigator_ck1 CHECK (((status)::text = ANY (ARRAY[('Active'::character varying)::text, ('Archive'::character varying)::text])))
);


ALTER TABLE ngscl_schema.primary_investigator OWNER TO ngscl_schema;

--
-- Name: pi16_primary_investigator; Type: VIEW; Schema: ngscl_schema; Owner: ngscl_schema
--

CREATE VIEW pi16_primary_investigator AS
 SELECT primary_investigator.primary_investigator_uid,
    primary_investigator.name,
    primary_investigator.email_address,
    primary_investigator.phone_number,
    primary_investigator.comments,
    primary_investigator.status
   FROM primary_investigator
  WHERE (primary_investigator.primary_investigator_uid = 16);


ALTER TABLE ngscl_schema.pi16_primary_investigator OWNER TO ngscl_schema;

--
-- Name: project; Type: TABLE; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

CREATE TABLE project (
    project_uid integer NOT NULL,
    project_name character varying(300) NOT NULL,
    primary_investigator_uid integer NOT NULL,
    status character varying(50) DEFAULT 'Active'::character varying NOT NULL,
    creation_date date DEFAULT ('now'::text)::date NOT NULL,
    project_prep_comments text,
    contact_uid integer,
    project_description text,
    admin_comments text,
    analysis_notes text,
    prepped_and_ready boolean DEFAULT false NOT NULL,
    assigned_to character varying(25),
    ref_run_type_uid integer DEFAULT 1 NOT NULL,
    CONSTRAINT project_ck1 CHECK (((status)::text = ANY (ARRAY[('Active'::character varying)::text, ('Completed'::character varying)::text, ('Holding'::character varying)::text])))
);


ALTER TABLE ngscl_schema.project OWNER TO ngscl_schema;

--
-- Name: pi16_project; Type: VIEW; Schema: ngscl_schema; Owner: ngscl_schema
--

CREATE VIEW pi16_project AS
 SELECT project.project_uid,
    project.project_name,
    project.primary_investigator_uid,
    project.ref_run_type_uid,
    project.status,
    project.creation_date,
    project.project_prep_comments,
    project.contact_uid,
    project.project_description,
    project.admin_comments,
    project.analysis_notes,
    project.prepped_and_ready,
    project.assigned_to
   FROM project
  WHERE (project.primary_investigator_uid = 16);


ALTER TABLE ngscl_schema.pi16_project OWNER TO ngscl_schema;

--
-- Name: project_log; Type: TABLE; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

CREATE TABLE project_log (
    project_log_uid integer NOT NULL,
    project_uid integer NOT NULL,
    name_of_experimenter text,
    experiment_title text,
    core_director_approval boolean DEFAULT false NOT NULL,
    core_director_approval_date date,
    primary_investigator_approval boolean DEFAULT false NOT NULL,
    primary_investigator_approval_date date,
    run_number integer,
    lanes text,
    run_date date,
    experiment_type text,
    results_summary text,
    account_number text,
    estimated_price double precision,
    estimated_price_comments text,
    hypothesis text,
    cells_used text,
    experimental_methods text,
    time_course text,
    experimental_category text,
    conditions_comments text,
    batching_instructions text,
    sample_purification_method text,
    kits_used text,
    amplification_method text,
    barcoding_used text,
    new_method_or_comparison_study text,
    library_prep_comments text,
    read_length text,
    adapter_sequences text,
    barcode_sequences text,
    CONSTRAINT project_log_ck1 CHECK ((run_number >= 1)),
    CONSTRAINT project_log_ck2 CHECK (((core_director_approval IS TRUE) OR (core_director_approval_date IS NULL))),
    CONSTRAINT project_log_ck3 CHECK (((primary_investigator_approval IS TRUE) OR (primary_investigator_approval_date IS NULL))),
    CONSTRAINT project_log_ck4 CHECK ((estimated_price >= (0)::double precision))
);


ALTER TABLE ngscl_schema.project_log OWNER TO ngscl_schema;

--
-- Name: pi16_project_log; Type: VIEW; Schema: ngscl_schema; Owner: ngscl_schema
--

CREATE VIEW pi16_project_log AS
 SELECT DISTINCT project_log.project_log_uid,
    project_log.project_uid,
    project_log.name_of_experimenter,
    project_log.experiment_title,
    project_log.core_director_approval,
    project_log.core_director_approval_date,
    project_log.primary_investigator_approval,
    project_log.primary_investigator_approval_date,
    project_log.run_number,
    project_log.lanes,
    project_log.run_date,
    project_log.experiment_type,
    project_log.results_summary,
    project_log.account_number,
    project_log.estimated_price,
    project_log.estimated_price_comments,
    project_log.hypothesis,
    project_log.cells_used,
    project_log.experimental_methods,
    project_log.time_course,
    project_log.experimental_category,
    project_log.conditions_comments,
    project_log.batching_instructions,
    project_log.sample_purification_method,
    project_log.kits_used,
    project_log.amplification_method,
    project_log.barcoding_used,
    project_log.new_method_or_comparison_study,
    project_log.library_prep_comments,
    project_log.read_length,
    project_log.adapter_sequences,
    project_log.barcode_sequences
   FROM project_log,
    project
  WHERE ((project_log.project_uid = project.project_uid) AND (project.primary_investigator_uid = 16));


ALTER TABLE ngscl_schema.pi16_project_log OWNER TO ngscl_schema;

--
-- Name: project_log_run_lane; Type: TABLE; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

CREATE TABLE project_log_run_lane (
    project_log_run_lane_uid integer NOT NULL,
    project_log_uid integer NOT NULL,
    sample_name character varying(40),
    sample_description text,
    species character varying(100) NOT NULL,
    batch_group character varying(15),
    CONSTRAINT project_log_run_lane_ck2 CHECK ((translate((sample_name)::text, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_'::text, 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX'::text) = rpad(''::text, length((sample_name)::text), 'X'::text)))
);


ALTER TABLE ngscl_schema.project_log_run_lane OWNER TO ngscl_schema;

--
-- Name: pi16_project_log_run_lane; Type: VIEW; Schema: ngscl_schema; Owner: ngscl_schema
--

CREATE VIEW pi16_project_log_run_lane AS
 SELECT project_log_run_lane.project_log_run_lane_uid,
    project_log_run_lane.project_log_uid,
    project_log_run_lane.batch_group,
    project_log_run_lane.sample_name,
    project_log_run_lane.sample_description,
    project_log_run_lane.species
   FROM project_log_run_lane,
    project_log,
    project
  WHERE (((project_log_run_lane.project_log_uid = project_log.project_log_uid) AND (project_log.project_uid = project.project_uid)) AND (project.primary_investigator_uid = 16));


ALTER TABLE ngscl_schema.pi16_project_log_run_lane OWNER TO ngscl_schema;

--
-- Name: run; Type: TABLE; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

CREATE TABLE run (
    run_uid integer NOT NULL,
    run_number integer NOT NULL,
    run_name character varying(300),
    read_type character varying(10) NOT NULL,
    read_1_length integer NOT NULL,
    read_2_length integer,
    read_length_indexing integer NOT NULL,
    hi_seq_slot character(1),
    cluster_gen_start_date date DEFAULT ('now'::text)::date,
    sequencing_start_date date DEFAULT ('now'::text)::date,
    truseq_cluster_gen_kit character varying(200),
    flow_cell_hs_id character varying(200),
    sequencing_kits text,
    comments text,
    ref_run_type_uid integer DEFAULT 1 NOT NULL,
    CONSTRAINT run_ck1 CHECK ((run_number > 0)),
    CONSTRAINT run_ck2 CHECK (((read_type)::text = ANY (ARRAY[('Single'::character varying)::text, ('Paired End'::character varying)::text]))),
    CONSTRAINT run_ck3 CHECK ((read_1_length > 0)),
    CONSTRAINT run_ck4 CHECK ((read_2_length > 0)),
    CONSTRAINT run_ck5 CHECK ((read_length_indexing > 0)),
    CONSTRAINT run_ck6 CHECK ((hi_seq_slot = ANY (ARRAY['A'::bpchar, 'B'::bpchar, NULL::bpchar]))),
    CONSTRAINT run_ck7 CHECK (((((read_type)::text = 'Single'::text) AND (read_2_length IS NULL)) OR (((read_type)::text = 'Paired End'::text) AND (read_2_length IS NOT NULL))))
);


ALTER TABLE ngscl_schema.run OWNER TO ngscl_schema;

--
-- Name: run_lane; Type: TABLE; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

CREATE TABLE run_lane (
    run_lane_uid integer NOT NULL,
    run_uid integer NOT NULL,
    lane_number integer NOT NULL,
    total_reads text,
    und_yield integer,
    und_percent_pf real,
    und_num_reads integer,
    und_percent_ge_q30_bases real,
    und_mean_quality_score real,
    und_percent_raw_clusters real,
    CONSTRAINT run_lane_ck1 CHECK (((lane_number >= 1) AND (lane_number <= 8))),
    CONSTRAINT run_lane_ck2 CHECK ((und_yield >= 0)),
    CONSTRAINT run_lane_ck3 CHECK (((und_percent_pf >= (0)::double precision) AND (und_percent_pf <= (100)::double precision))),
    CONSTRAINT run_lane_ck4 CHECK ((und_num_reads >= 0)),
    CONSTRAINT run_lane_ck5 CHECK (((und_percent_raw_clusters >= (0)::double precision) AND (und_percent_raw_clusters <= (100)::double precision))),
    CONSTRAINT run_lane_ck6 CHECK (((und_percent_ge_q30_bases >= (0)::double precision) AND (und_percent_ge_q30_bases <= (100)::double precision))),
    CONSTRAINT run_lane_ck7 CHECK ((und_mean_quality_score >= (0)::double precision))
);


ALTER TABLE ngscl_schema.run_lane OWNER TO ngscl_schema;

--
-- Name: run_lane_sample; Type: TABLE; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

CREATE TABLE run_lane_sample (
    run_lane_sample_uid integer NOT NULL,
    run_lane_uid integer NOT NULL,
    sample_uid integer NOT NULL,
    comments text,
    insert_size character varying(20),
    load_concentration real,
    billing text,
    paid boolean DEFAULT false,
    yield integer,
    percent_pf real,
    num_reads integer,
    percent_ge_q30_bases real,
    mean_quality_score real,
    percent_raw_clusters real,
    CONSTRAINT run_lane_sample_ck1 CHECK ((load_concentration > (0)::double precision)),
    CONSTRAINT run_lane_sample_ck2 CHECK ((yield >= 0)),
    CONSTRAINT run_lane_sample_ck3 CHECK (((percent_pf >= (0)::double precision) AND (percent_pf <= (100)::double precision))),
    CONSTRAINT run_lane_sample_ck4 CHECK ((num_reads >= 0)),
    CONSTRAINT run_lane_sample_ck5 CHECK (((percent_raw_clusters >= (0)::double precision) AND (percent_raw_clusters <= (100)::double precision))),
    CONSTRAINT run_lane_sample_ck6 CHECK (((percent_ge_q30_bases >= (0)::double precision) AND (percent_ge_q30_bases <= (100)::double precision))),
    CONSTRAINT run_lane_sample_ck7 CHECK ((mean_quality_score >= (0)::double precision))
);


ALTER TABLE ngscl_schema.run_lane_sample OWNER TO ngscl_schema;

--
-- Name: sample; Type: TABLE; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

CREATE TABLE sample (
    sample_uid integer NOT NULL,
    project_uid integer NOT NULL,
    sample_name character varying(40) NOT NULL,
    barcode character varying(50) NOT NULL,
    species character varying(100) NOT NULL,
    sample_type character varying(150) NOT NULL,
    comments text,
    status character varying(50) DEFAULT 'Active'::character varying NOT NULL,
    barcode_index character varying(150),
    sample_description text,
    batch_group character varying(15),
    concentration real,
    volume real,
    rna_integrity_number real,
    CONSTRAINT sample_ck1 CHECK (((status)::text = ANY (ARRAY[('Active'::character varying)::text, ('Holding'::character varying)::text, ('Archive'::character varying)::text, ('Trash'::character varying)::text]))),
    CONSTRAINT sample_ck2 CHECK ((translate((sample_name)::text, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_-'::text, 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX'::text) = rpad(''::text, length((sample_name)::text), 'X'::text))),
    CONSTRAINT sample_ck3 CHECK ((translate((species)::text, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_. '::text, 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX'::text) = rpad(''::text, length((species)::text), 'X'::text))),
    CONSTRAINT sample_ck4 CHECK ((translate((barcode)::text, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_'::text, 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX'::text) = rpad(''::text, length((barcode)::text), 'X'::text))),
    CONSTRAINT sample_ck5 CHECK ((translate((barcode_index)::text, 'ACGT-'::text, 'XXXXX'::text) = rpad(''::text, length((barcode_index)::text), 'X'::text))),
    CONSTRAINT sample_ck6 CHECK ((concentration >= (0)::double precision)),
    CONSTRAINT sample_ck7 CHECK ((volume >= (0)::double precision)),
    CONSTRAINT sample_ck8 CHECK (((rna_integrity_number >= (0)::double precision) AND (rna_integrity_number <= (10)::double precision)))
);


ALTER TABLE ngscl_schema.sample OWNER TO ngscl_schema;

--
-- Name: pi16_run; Type: VIEW; Schema: ngscl_schema; Owner: ngscl_schema
--

CREATE VIEW pi16_run AS
 SELECT DISTINCT run.run_uid,
    run.run_number,
    run.run_name,
    run.ref_run_type_uid,
    run.read_type,
    run.read_1_length,
    run.read_2_length,
    run.read_length_indexing,
    run.hi_seq_slot,
    run.cluster_gen_start_date,
    run.sequencing_start_date,
    run.truseq_cluster_gen_kit,
    run.flow_cell_hs_id,
    run.sequencing_kits,
    run.comments
   FROM run,
    run_lane,
    run_lane_sample,
    sample,
    project
  WHERE (((((run.run_uid = run_lane.run_uid) AND (run_lane.run_lane_uid = run_lane_sample.run_lane_uid)) AND (sample.sample_uid = run_lane_sample.sample_uid)) AND (sample.project_uid = project.project_uid)) AND (project.primary_investigator_uid = 16));


ALTER TABLE ngscl_schema.pi16_run OWNER TO ngscl_schema;

--
-- Name: pi16_run_lane; Type: VIEW; Schema: ngscl_schema; Owner: ngscl_schema
--

CREATE VIEW pi16_run_lane AS
 SELECT DISTINCT run_lane.run_lane_uid,
    run_lane.run_uid,
    run_lane.lane_number,
    run_lane.total_reads,
    run_lane.und_yield,
    run_lane.und_percent_pf,
    run_lane.und_num_reads,
    run_lane.und_percent_raw_clusters,
    run_lane.und_percent_ge_q30_bases,
    run_lane.und_mean_quality_score
   FROM run_lane,
    run_lane_sample,
    sample,
    project
  WHERE ((((run_lane.run_lane_uid = run_lane_sample.run_lane_uid) AND (sample.sample_uid = run_lane_sample.sample_uid)) AND (sample.project_uid = project.project_uid)) AND (project.primary_investigator_uid = 16));


ALTER TABLE ngscl_schema.pi16_run_lane OWNER TO ngscl_schema;

--
-- Name: pi16_run_lane_sample; Type: VIEW; Schema: ngscl_schema; Owner: ngscl_schema
--

CREATE VIEW pi16_run_lane_sample AS
 SELECT DISTINCT run_lane_sample.run_lane_sample_uid,
    run_lane_sample.run_lane_uid,
    run_lane_sample.sample_uid,
    run_lane_sample.comments,
    run_lane_sample.insert_size,
    run_lane_sample.load_concentration,
    run_lane_sample.billing,
    run_lane_sample.paid,
    run_lane_sample.yield,
    run_lane_sample.percent_pf,
    run_lane_sample.num_reads,
    run_lane_sample.percent_raw_clusters,
    run_lane_sample.percent_ge_q30_bases,
    run_lane_sample.mean_quality_score
   FROM run_lane_sample,
    sample,
    project
  WHERE (((sample.sample_uid = run_lane_sample.sample_uid) AND (sample.project_uid = project.project_uid)) AND (project.primary_investigator_uid = 16));


ALTER TABLE ngscl_schema.pi16_run_lane_sample OWNER TO ngscl_schema;

--
-- Name: pi16_sample; Type: VIEW; Schema: ngscl_schema; Owner: ngscl_schema
--

CREATE VIEW pi16_sample AS
 SELECT DISTINCT sample.sample_uid,
    sample.project_uid,
    sample.sample_name,
    sample.batch_group,
    sample.barcode,
    sample.species,
    sample.sample_type,
    sample.comments,
    sample.status,
    sample.barcode_index,
    sample.sample_description,
    sample.concentration,
    sample.volume,
    sample.rna_integrity_number
   FROM sample,
    project
  WHERE ((sample.project_uid = project.project_uid) AND (project.primary_investigator_uid = 16));


ALTER TABLE ngscl_schema.pi16_sample OWNER TO ngscl_schema;

--
-- Name: pi_ngscl_role; Type: VIEW; Schema: ngscl_schema; Owner: ngscl_schema
--

CREATE VIEW pi_ngscl_role AS
 SELECT ngscl_role.ngscl_role_uid,
    ngscl_role.ngscl_role,
    ngscl_role.primary_investigator_uid
   FROM ngscl_role
  WHERE ((ngscl_role.ngscl_role)::name = "current_user"());


ALTER TABLE ngscl_schema.pi_ngscl_role OWNER TO ngscl_schema;

--
-- Name: primary_investigator_primary_investigator_uid_seq; Type: SEQUENCE; Schema: ngscl_schema; Owner: ngscl_schema
--

CREATE SEQUENCE primary_investigator_primary_investigator_uid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE ngscl_schema.primary_investigator_primary_investigator_uid_seq OWNER TO ngscl_schema;

--
-- Name: primary_investigator_primary_investigator_uid_seq; Type: SEQUENCE OWNED BY; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER SEQUENCE primary_investigator_primary_investigator_uid_seq OWNED BY primary_investigator.primary_investigator_uid;


--
-- Name: project_log_project_log_uid_seq; Type: SEQUENCE; Schema: ngscl_schema; Owner: ngscl_schema
--

CREATE SEQUENCE project_log_project_log_uid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE ngscl_schema.project_log_project_log_uid_seq OWNER TO ngscl_schema;

--
-- Name: project_log_project_log_uid_seq; Type: SEQUENCE OWNED BY; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER SEQUENCE project_log_project_log_uid_seq OWNED BY project_log.project_log_uid;


--
-- Name: project_log_run_lane_project_log_run_lane_uid_seq; Type: SEQUENCE; Schema: ngscl_schema; Owner: ngscl_schema
--

CREATE SEQUENCE project_log_run_lane_project_log_run_lane_uid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE ngscl_schema.project_log_run_lane_project_log_run_lane_uid_seq OWNER TO ngscl_schema;

--
-- Name: project_log_run_lane_project_log_run_lane_uid_seq; Type: SEQUENCE OWNED BY; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER SEQUENCE project_log_run_lane_project_log_run_lane_uid_seq OWNED BY project_log_run_lane.project_log_run_lane_uid;


--
-- Name: project_project_uid_seq; Type: SEQUENCE; Schema: ngscl_schema; Owner: ngscl_schema
--

CREATE SEQUENCE project_project_uid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE ngscl_schema.project_project_uid_seq OWNER TO ngscl_schema;

--
-- Name: project_project_uid_seq; Type: SEQUENCE OWNED BY; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER SEQUENCE project_project_uid_seq OWNED BY project.project_uid;


--
-- Name: ref_archive_name; Type: TABLE; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

CREATE TABLE ref_archive_name (
    ref_archive_name_uid integer NOT NULL,
    archive_name character varying(50) NOT NULL
);


ALTER TABLE ngscl_schema.ref_archive_name OWNER TO ngscl_schema;

--
-- Name: ref_archive_name_ref_archive_name_uid_seq; Type: SEQUENCE; Schema: ngscl_schema; Owner: ngscl_schema
--

CREATE SEQUENCE ref_archive_name_ref_archive_name_uid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE ngscl_schema.ref_archive_name_ref_archive_name_uid_seq OWNER TO ngscl_schema;

--
-- Name: ref_archive_name_ref_archive_name_uid_seq; Type: SEQUENCE OWNED BY; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER SEQUENCE ref_archive_name_ref_archive_name_uid_seq OWNED BY ref_archive_name.ref_archive_name_uid;


--
-- Name: ref_barcode; Type: TABLE; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

CREATE TABLE ref_barcode (
    ref_barcode_uid integer NOT NULL,
    ref_prep_type_uid integer NOT NULL,
    barcode_number integer NOT NULL,
    barcode_index character varying(150) NOT NULL,
    CONSTRAINT ref_barcode_ck1 CHECK ((translate((barcode_index)::text, 'ACGT-'::text, 'XXXXX'::text) = rpad(''::text, length((barcode_index)::text), 'X'::text)))
);


ALTER TABLE ngscl_schema.ref_barcode OWNER TO ngscl_schema;

--
-- Name: ref_barcode_ref_barcode_uid_seq; Type: SEQUENCE; Schema: ngscl_schema; Owner: ngscl_schema
--

CREATE SEQUENCE ref_barcode_ref_barcode_uid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE ngscl_schema.ref_barcode_ref_barcode_uid_seq OWNER TO ngscl_schema;

--
-- Name: ref_barcode_ref_barcode_uid_seq; Type: SEQUENCE OWNED BY; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER SEQUENCE ref_barcode_ref_barcode_uid_seq OWNED BY ref_barcode.ref_barcode_uid;


--
-- Name: ref_prep_type; Type: TABLE; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

CREATE TABLE ref_prep_type (
    ref_prep_type_uid integer NOT NULL,
    prep_type character varying(50),
    CONSTRAINT ref_prep_type_ck1 CHECK ((translate((prep_type)::text, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_'::text, 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX'::text) = rpad(''::text, length((prep_type)::text), 'X'::text)))
);


ALTER TABLE ngscl_schema.ref_prep_type OWNER TO ngscl_schema;

--
-- Name: ref_prep_type_ref_prep_type_uid_seq; Type: SEQUENCE; Schema: ngscl_schema; Owner: ngscl_schema
--

CREATE SEQUENCE ref_prep_type_ref_prep_type_uid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE ngscl_schema.ref_prep_type_ref_prep_type_uid_seq OWNER TO ngscl_schema;

--
-- Name: ref_prep_type_ref_prep_type_uid_seq; Type: SEQUENCE OWNED BY; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER SEQUENCE ref_prep_type_ref_prep_type_uid_seq OWNED BY ref_prep_type.ref_prep_type_uid;


--
-- Name: ref_run_type; Type: TABLE; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

CREATE TABLE ref_run_type (
    ref_run_type_uid integer NOT NULL,
    run_type character varying(50) NOT NULL
);


ALTER TABLE ngscl_schema.ref_run_type OWNER TO ngscl_schema;

--
-- Name: ref_run_type_ref_run_type_uid_seq; Type: SEQUENCE; Schema: ngscl_schema; Owner: ngscl_schema
--

CREATE SEQUENCE ref_run_type_ref_run_type_uid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE ngscl_schema.ref_run_type_ref_run_type_uid_seq OWNER TO ngscl_schema;

--
-- Name: ref_run_type_ref_run_type_uid_seq; Type: SEQUENCE OWNED BY; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER SEQUENCE ref_run_type_ref_run_type_uid_seq OWNED BY ref_run_type.ref_run_type_uid;


--
-- Name: run_lane_run_lane_uid_seq; Type: SEQUENCE; Schema: ngscl_schema; Owner: ngscl_schema
--

CREATE SEQUENCE run_lane_run_lane_uid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE ngscl_schema.run_lane_run_lane_uid_seq OWNER TO ngscl_schema;

--
-- Name: run_lane_run_lane_uid_seq; Type: SEQUENCE OWNED BY; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER SEQUENCE run_lane_run_lane_uid_seq OWNED BY run_lane.run_lane_uid;


--
-- Name: run_lane_sample_run_lane_sample_uid_seq; Type: SEQUENCE; Schema: ngscl_schema; Owner: ngscl_schema
--

CREATE SEQUENCE run_lane_sample_run_lane_sample_uid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE ngscl_schema.run_lane_sample_run_lane_sample_uid_seq OWNER TO ngscl_schema;

--
-- Name: run_lane_sample_run_lane_sample_uid_seq; Type: SEQUENCE OWNED BY; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER SEQUENCE run_lane_sample_run_lane_sample_uid_seq OWNED BY run_lane_sample.run_lane_sample_uid;


--
-- Name: run_run_uid_seq; Type: SEQUENCE; Schema: ngscl_schema; Owner: ngscl_schema
--

CREATE SEQUENCE run_run_uid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE ngscl_schema.run_run_uid_seq OWNER TO ngscl_schema;

--
-- Name: run_run_uid_seq; Type: SEQUENCE OWNED BY; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER SEQUENCE run_run_uid_seq OWNED BY run.run_uid;


--
-- Name: sample_sample_uid_seq; Type: SEQUENCE; Schema: ngscl_schema; Owner: ngscl_schema
--

CREATE SEQUENCE sample_sample_uid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE ngscl_schema.sample_sample_uid_seq OWNER TO ngscl_schema;

--
-- Name: sample_sample_uid_seq; Type: SEQUENCE OWNED BY; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER SEQUENCE sample_sample_uid_seq OWNED BY sample.sample_uid;


--
-- Name: archive_uid; Type: DEFAULT; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER TABLE ONLY archive ALTER COLUMN archive_uid SET DEFAULT nextval('archive_archive_uid_seq'::regclass);


--
-- Name: contact_uid; Type: DEFAULT; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER TABLE ONLY contact ALTER COLUMN contact_uid SET DEFAULT nextval('contact_contact_uid_seq'::regclass);


--
-- Name: deleted_project_log_uid; Type: DEFAULT; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER TABLE ONLY deleted_project_log ALTER COLUMN deleted_project_log_uid SET DEFAULT nextval('deleted_project_log_deleted_project_log_uid_seq'::regclass);


--
-- Name: deleted_project_log_run_lane_uid; Type: DEFAULT; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER TABLE ONLY deleted_project_log_run_lane ALTER COLUMN deleted_project_log_run_lane_uid SET DEFAULT nextval('deleted_project_log_run_lane_deleted_project_log_run_lane_u_seq'::regclass);


--
-- Name: library_prep_note_uid; Type: DEFAULT; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER TABLE ONLY library_prep_note ALTER COLUMN library_prep_note_uid SET DEFAULT nextval('library_prep_note_library_prep_note_uid_seq'::regclass);


--
-- Name: library_prep_note_image_uid; Type: DEFAULT; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER TABLE ONLY library_prep_note_image ALTER COLUMN library_prep_note_image_uid SET DEFAULT nextval('library_prep_note_image_library_prep_note_image_uid_seq'::regclass);


--
-- Name: library_prep_note_sample_uid; Type: DEFAULT; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER TABLE ONLY library_prep_note_sample ALTER COLUMN library_prep_note_sample_uid SET DEFAULT nextval('library_prep_note_sample_library_prep_note_sample_uid_seq'::regclass);


--
-- Name: ngscl_role_uid; Type: DEFAULT; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER TABLE ONLY ngscl_role ALTER COLUMN ngscl_role_uid SET DEFAULT nextval('ngscl_role_ngscl_role_uid_seq'::regclass);


--
-- Name: notification_address_uid; Type: DEFAULT; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER TABLE ONLY notification_address ALTER COLUMN notification_address_uid SET DEFAULT nextval('notification_address_notification_address_uid_seq'::regclass);


--
-- Name: primary_investigator_uid; Type: DEFAULT; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER TABLE ONLY primary_investigator ALTER COLUMN primary_investigator_uid SET DEFAULT nextval('primary_investigator_primary_investigator_uid_seq'::regclass);


--
-- Name: project_uid; Type: DEFAULT; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER TABLE ONLY project ALTER COLUMN project_uid SET DEFAULT nextval('project_project_uid_seq'::regclass);


--
-- Name: project_log_uid; Type: DEFAULT; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER TABLE ONLY project_log ALTER COLUMN project_log_uid SET DEFAULT nextval('project_log_project_log_uid_seq'::regclass);


--
-- Name: project_log_run_lane_uid; Type: DEFAULT; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER TABLE ONLY project_log_run_lane ALTER COLUMN project_log_run_lane_uid SET DEFAULT nextval('project_log_run_lane_project_log_run_lane_uid_seq'::regclass);


--
-- Name: ref_archive_name_uid; Type: DEFAULT; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER TABLE ONLY ref_archive_name ALTER COLUMN ref_archive_name_uid SET DEFAULT nextval('ref_archive_name_ref_archive_name_uid_seq'::regclass);


--
-- Name: ref_barcode_uid; Type: DEFAULT; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER TABLE ONLY ref_barcode ALTER COLUMN ref_barcode_uid SET DEFAULT nextval('ref_barcode_ref_barcode_uid_seq'::regclass);


--
-- Name: ref_prep_type_uid; Type: DEFAULT; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER TABLE ONLY ref_prep_type ALTER COLUMN ref_prep_type_uid SET DEFAULT nextval('ref_prep_type_ref_prep_type_uid_seq'::regclass);


--
-- Name: ref_run_type_uid; Type: DEFAULT; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER TABLE ONLY ref_run_type ALTER COLUMN ref_run_type_uid SET DEFAULT nextval('ref_run_type_ref_run_type_uid_seq'::regclass);


--
-- Name: run_uid; Type: DEFAULT; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER TABLE ONLY run ALTER COLUMN run_uid SET DEFAULT nextval('run_run_uid_seq'::regclass);


--
-- Name: run_lane_uid; Type: DEFAULT; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER TABLE ONLY run_lane ALTER COLUMN run_lane_uid SET DEFAULT nextval('run_lane_run_lane_uid_seq'::regclass);


--
-- Name: run_lane_sample_uid; Type: DEFAULT; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER TABLE ONLY run_lane_sample ALTER COLUMN run_lane_sample_uid SET DEFAULT nextval('run_lane_sample_run_lane_sample_uid_seq'::regclass);


--
-- Name: sample_uid; Type: DEFAULT; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER TABLE ONLY sample ALTER COLUMN sample_uid SET DEFAULT nextval('sample_sample_uid_seq'::regclass);


--
-- Name: archive_ak1; Type: CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

ALTER TABLE ONLY archive
    ADD CONSTRAINT archive_ak1 UNIQUE (sample_uid, ref_archive_name_uid);


--
-- Name: archive_pk; Type: CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

ALTER TABLE ONLY archive
    ADD CONSTRAINT archive_pk PRIMARY KEY (archive_uid);


--
-- Name: contact_ak1; Type: CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

ALTER TABLE ONLY contact
    ADD CONSTRAINT contact_ak1 UNIQUE (name, primary_investigator_uid);


--
-- Name: contact_pk; Type: CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

ALTER TABLE ONLY contact
    ADD CONSTRAINT contact_pk PRIMARY KEY (contact_uid);


--
-- Name: deleted_project_log_ak1; Type: CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

ALTER TABLE ONLY deleted_project_log
    ADD CONSTRAINT deleted_project_log_ak1 UNIQUE (project_uid, deleted_timestamp);


--
-- Name: deleted_project_log_pk; Type: CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

ALTER TABLE ONLY deleted_project_log
    ADD CONSTRAINT deleted_project_log_pk PRIMARY KEY (deleted_project_log_uid);


--
-- Name: deleted_project_log_run_lane_pk; Type: CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

ALTER TABLE ONLY deleted_project_log_run_lane
    ADD CONSTRAINT deleted_project_log_run_lane_pk PRIMARY KEY (deleted_project_log_run_lane_uid);


--
-- Name: library_prep_note_ak1; Type: CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

ALTER TABLE ONLY library_prep_note
    ADD CONSTRAINT library_prep_note_ak1 UNIQUE (library_prep_note_name, creation_date);


--
-- Name: library_prep_note_image_ak1; Type: CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

ALTER TABLE ONLY library_prep_note_image
    ADD CONSTRAINT library_prep_note_image_ak1 UNIQUE (library_prep_note_uid, image_number);


--
-- Name: library_prep_note_image_pk; Type: CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

ALTER TABLE ONLY library_prep_note_image
    ADD CONSTRAINT library_prep_note_image_pk PRIMARY KEY (library_prep_note_image_uid);


--
-- Name: library_prep_note_pk; Type: CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

ALTER TABLE ONLY library_prep_note
    ADD CONSTRAINT library_prep_note_pk PRIMARY KEY (library_prep_note_uid);


--
-- Name: library_prep_note_sample_ak1; Type: CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

ALTER TABLE ONLY library_prep_note_sample
    ADD CONSTRAINT library_prep_note_sample_ak1 UNIQUE (library_prep_note_uid, sample_uid);


--
-- Name: library_prep_note_sample_pk; Type: CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

ALTER TABLE ONLY library_prep_note_sample
    ADD CONSTRAINT library_prep_note_sample_pk PRIMARY KEY (library_prep_note_sample_uid);


--
-- Name: ngscl_role_ak1; Type: CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

ALTER TABLE ONLY ngscl_role
    ADD CONSTRAINT ngscl_role_ak1 UNIQUE (ngscl_role);


--
-- Name: ngscl_role_pk; Type: CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

ALTER TABLE ONLY ngscl_role
    ADD CONSTRAINT ngscl_role_pk PRIMARY KEY (ngscl_role_uid);


--
-- Name: notification_address_ak1; Type: CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

ALTER TABLE ONLY notification_address
    ADD CONSTRAINT notification_address_ak1 UNIQUE (username, domain_name);


--
-- Name: notification_address_pk; Type: CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

ALTER TABLE ONLY notification_address
    ADD CONSTRAINT notification_address_pk PRIMARY KEY (notification_address_uid);


--
-- Name: primary_investigator_ak1; Type: CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

ALTER TABLE ONLY primary_investigator
    ADD CONSTRAINT primary_investigator_ak1 UNIQUE (name);


--
-- Name: primary_investigator_pk; Type: CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

ALTER TABLE ONLY primary_investigator
    ADD CONSTRAINT primary_investigator_pk PRIMARY KEY (primary_investigator_uid);


--
-- Name: project_ak1; Type: CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

ALTER TABLE ONLY project
    ADD CONSTRAINT project_ak1 UNIQUE (project_name);


--
-- Name: project_log_ak1; Type: CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

ALTER TABLE ONLY project_log
    ADD CONSTRAINT project_log_ak1 UNIQUE (project_uid);


--
-- Name: project_log_pk; Type: CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

ALTER TABLE ONLY project_log
    ADD CONSTRAINT project_log_pk PRIMARY KEY (project_log_uid);


--
-- Name: project_log_run_lane_ak1; Type: CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

ALTER TABLE ONLY project_log_run_lane
    ADD CONSTRAINT project_log_run_lane_ak1 UNIQUE (project_log_uid, sample_name);


--
-- Name: project_log_run_lane_pk; Type: CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

ALTER TABLE ONLY project_log_run_lane
    ADD CONSTRAINT project_log_run_lane_pk PRIMARY KEY (project_log_run_lane_uid);


--
-- Name: project_pk; Type: CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

ALTER TABLE ONLY project
    ADD CONSTRAINT project_pk PRIMARY KEY (project_uid);


--
-- Name: ref_archive_name_ak1; Type: CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

ALTER TABLE ONLY ref_archive_name
    ADD CONSTRAINT ref_archive_name_ak1 UNIQUE (archive_name);


--
-- Name: ref_archive_name_pk; Type: CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

ALTER TABLE ONLY ref_archive_name
    ADD CONSTRAINT ref_archive_name_pk PRIMARY KEY (ref_archive_name_uid);


--
-- Name: ref_barcode_ak1; Type: CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

ALTER TABLE ONLY ref_barcode
    ADD CONSTRAINT ref_barcode_ak1 UNIQUE (ref_prep_type_uid, barcode_number);


--
-- Name: ref_barcode_pk; Type: CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

ALTER TABLE ONLY ref_barcode
    ADD CONSTRAINT ref_barcode_pk PRIMARY KEY (ref_barcode_uid);


--
-- Name: ref_prep_type_ak1; Type: CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

ALTER TABLE ONLY ref_prep_type
    ADD CONSTRAINT ref_prep_type_ak1 UNIQUE (prep_type);


--
-- Name: ref_prep_type_pk; Type: CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

ALTER TABLE ONLY ref_prep_type
    ADD CONSTRAINT ref_prep_type_pk PRIMARY KEY (ref_prep_type_uid);


--
-- Name: ref_run_type_ak1; Type: CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

ALTER TABLE ONLY ref_run_type
    ADD CONSTRAINT ref_run_type_ak1 UNIQUE (run_type);


--
-- Name: ref_run_type_pk1; Type: CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

ALTER TABLE ONLY ref_run_type
    ADD CONSTRAINT ref_run_type_pk1 PRIMARY KEY (ref_run_type_uid);


--
-- Name: run_ak1; Type: CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

ALTER TABLE ONLY run
    ADD CONSTRAINT run_ak1 UNIQUE (run_number, ref_run_type_uid);


--
-- Name: run_lane_ak1; Type: CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

ALTER TABLE ONLY run_lane
    ADD CONSTRAINT run_lane_ak1 UNIQUE (run_uid, lane_number);


--
-- Name: run_lane_pk; Type: CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

ALTER TABLE ONLY run_lane
    ADD CONSTRAINT run_lane_pk PRIMARY KEY (run_lane_uid);


--
-- Name: run_lane_sample_ak1; Type: CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

ALTER TABLE ONLY run_lane_sample
    ADD CONSTRAINT run_lane_sample_ak1 UNIQUE (run_lane_uid, sample_uid);


--
-- Name: run_lane_sample_pk; Type: CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

ALTER TABLE ONLY run_lane_sample
    ADD CONSTRAINT run_lane_sample_pk PRIMARY KEY (run_lane_sample_uid);


--
-- Name: run_pk; Type: CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

ALTER TABLE ONLY run
    ADD CONSTRAINT run_pk PRIMARY KEY (run_uid);


--
-- Name: sample_ak1; Type: CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

ALTER TABLE ONLY sample
    ADD CONSTRAINT sample_ak1 UNIQUE (project_uid, sample_name);


--
-- Name: sample_pk; Type: CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema; Tablespace: 
--

ALTER TABLE ONLY sample
    ADD CONSTRAINT sample_pk PRIMARY KEY (sample_uid);


--
-- Name: pi16_project_log_del; Type: RULE; Schema: ngscl_schema; Owner: ngscl_schema
--

CREATE RULE pi16_project_log_del AS
    ON DELETE TO pi16_project_log DO INSTEAD  DELETE FROM project_log
  WHERE (project_log.project_log_uid = old.project_log_uid);


--
-- Name: pi16_project_log_ins; Type: RULE; Schema: ngscl_schema; Owner: ngscl_schema
--

CREATE RULE pi16_project_log_ins AS
    ON INSERT TO pi16_project_log DO INSTEAD  INSERT INTO project_log (project_log_uid, project_uid, name_of_experimenter, experiment_title, core_director_approval, core_director_approval_date, primary_investigator_approval, primary_investigator_approval_date, run_number, lanes, run_date, experiment_type, results_summary, estimated_price, estimated_price_comments, hypothesis, cells_used, experimental_methods, time_course, experimental_category, conditions_comments, batching_instructions, sample_purification_method, kits_used, amplification_method, barcoding_used, new_method_or_comparison_study, library_prep_comments, read_length, adapter_sequences, barcode_sequences)
  VALUES (new.project_log_uid, new.project_uid, new.name_of_experimenter, new.experiment_title, new.core_director_approval, new.core_director_approval_date, new.primary_investigator_approval, new.primary_investigator_approval_date, new.run_number, new.lanes, new.run_date, new.experiment_type, new.results_summary, new.estimated_price, new.estimated_price_comments, new.hypothesis, new.cells_used, new.experimental_methods, new.time_course, new.experimental_category, new.conditions_comments, new.batching_instructions, new.sample_purification_method, new.kits_used, new.amplification_method, new.barcoding_used, new.new_method_or_comparison_study, new.library_prep_comments, new.read_length, new.adapter_sequences, new.barcode_sequences);


--
-- Name: pi16_project_log_run_lane_del; Type: RULE; Schema: ngscl_schema; Owner: ngscl_schema
--

CREATE RULE pi16_project_log_run_lane_del AS
    ON DELETE TO pi16_project_log_run_lane DO INSTEAD  DELETE FROM project_log_run_lane
  WHERE (project_log_run_lane.project_log_run_lane_uid = old.project_log_run_lane_uid);


--
-- Name: pi16_project_log_run_lane_ins; Type: RULE; Schema: ngscl_schema; Owner: ngscl_schema
--

CREATE RULE pi16_project_log_run_lane_ins AS
    ON INSERT TO pi16_project_log_run_lane DO INSTEAD  INSERT INTO project_log_run_lane (project_log_run_lane_uid, project_log_uid, batch_group, sample_name, sample_description, species)
  VALUES (new.project_log_run_lane_uid, new.project_log_uid, new.batch_group, new.sample_name, new.sample_description, new.species);


--
-- Name: pi16_project_log_run_lane_upd; Type: RULE; Schema: ngscl_schema; Owner: ngscl_schema
--

CREATE RULE pi16_project_log_run_lane_upd AS
    ON UPDATE TO pi16_project_log_run_lane DO INSTEAD  UPDATE project_log_run_lane SET project_log_run_lane_uid = new.project_log_run_lane_uid, project_log_uid = new.project_log_uid, batch_group = new.batch_group, sample_name = new.sample_name, sample_description = new.sample_description, species = new.species
  WHERE (project_log_run_lane.project_log_run_lane_uid = old.project_log_run_lane_uid);


--
-- Name: pi16_project_log_upd; Type: RULE; Schema: ngscl_schema; Owner: ngscl_schema
--

CREATE RULE pi16_project_log_upd AS
    ON UPDATE TO pi16_project_log DO INSTEAD  UPDATE project_log SET project_log_uid = new.project_log_uid, project_uid = new.project_uid, name_of_experimenter = new.name_of_experimenter, experiment_title = new.experiment_title, core_director_approval = new.core_director_approval, core_director_approval_date = new.core_director_approval_date, primary_investigator_approval = new.primary_investigator_approval, primary_investigator_approval_date = new.primary_investigator_approval_date, run_number = new.run_number, lanes = new.lanes, run_date = new.run_date, experiment_type = new.experiment_type, results_summary = new.results_summary, estimated_price = new.estimated_price, estimated_price_comments = new.estimated_price_comments, hypothesis = new.hypothesis, cells_used = new.cells_used, experimental_methods = new.experimental_methods, time_course = new.time_course, experimental_category = new.experimental_category, conditions_comments = new.conditions_comments, batching_instructions = new.batching_instructions, sample_purification_method = new.sample_purification_method, kits_used = new.kits_used, amplification_method = new.amplification_method, barcoding_used = new.barcoding_used, new_method_or_comparison_study = new.new_method_or_comparison_study, library_prep_comments = new.library_prep_comments, read_length = new.read_length, adapter_sequences = new.adapter_sequences, barcode_sequences = new.barcode_sequences
  WHERE (project_log.project_log_uid = old.project_log_uid);


--
-- Name: deleted_project_log_deleted_project_log_run_lane_fk1; Type: FK CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER TABLE ONLY deleted_project_log_run_lane
    ADD CONSTRAINT deleted_project_log_deleted_project_log_run_lane_fk1 FOREIGN KEY (deleted_project_log_uid) REFERENCES deleted_project_log(deleted_project_log_uid);


--
-- Name: ngscl_role_fk1; Type: FK CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER TABLE ONLY ngscl_role
    ADD CONSTRAINT ngscl_role_fk1 FOREIGN KEY (primary_investigator_uid) REFERENCES primary_investigator(primary_investigator_uid);


--
-- Name: project_log_project_log_run_lane_fk1; Type: FK CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER TABLE ONLY project_log_run_lane
    ADD CONSTRAINT project_log_project_log_run_lane_fk1 FOREIGN KEY (project_log_uid) REFERENCES project_log(project_log_uid);


--
-- Name: project_project_log_fk1; Type: FK CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER TABLE ONLY project_log
    ADD CONSTRAINT project_project_log_fk1 FOREIGN KEY (project_uid) REFERENCES project(project_uid);


--
-- Name: ref_barcode_fk1; Type: FK CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER TABLE ONLY ref_barcode
    ADD CONSTRAINT ref_barcode_fk1 FOREIGN KEY (ref_prep_type_uid) REFERENCES ref_prep_type(ref_prep_type_uid);


--
-- Name: ref_run_type_project_fk1; Type: FK CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER TABLE ONLY project
    ADD CONSTRAINT ref_run_type_project_fk1 FOREIGN KEY (ref_run_type_uid) REFERENCES ref_run_type(ref_run_type_uid);


--
-- Name: ref_run_type_run_fk1; Type: FK CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER TABLE ONLY run
    ADD CONSTRAINT ref_run_type_run_fk1 FOREIGN KEY (ref_run_type_uid) REFERENCES ref_run_type(ref_run_type_uid);


--
-- Name: run_lane_run_lane_sample_fk1; Type: FK CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER TABLE ONLY run_lane_sample
    ADD CONSTRAINT run_lane_run_lane_sample_fk1 FOREIGN KEY (run_lane_uid) REFERENCES run_lane(run_lane_uid);


--
-- Name: run_run_lane_fk1; Type: FK CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER TABLE ONLY run_lane
    ADD CONSTRAINT run_run_lane_fk1 FOREIGN KEY (run_uid) REFERENCES run(run_uid);


--
-- Name: sample_run_lane_sample_fk1; Type: FK CONSTRAINT; Schema: ngscl_schema; Owner: ngscl_schema
--

ALTER TABLE ONLY run_lane_sample
    ADD CONSTRAINT sample_run_lane_sample_fk1 FOREIGN KEY (sample_uid) REFERENCES sample(sample_uid);


--
-- Name: ngscl_schema; Type: ACL; Schema: -; Owner: ngscl_schema
--

REVOKE ALL ON SCHEMA ngscl_schema FROM PUBLIC;
REVOKE ALL ON SCHEMA ngscl_schema FROM ngscl_schema;
GRANT ALL ON SCHEMA ngscl_schema TO ngscl_schema;
GRANT USAGE ON SCHEMA ngscl_schema TO dac_grants;
GRANT USAGE ON SCHEMA ngscl_schema TO pi_user;


--
-- Name: export_prep_note_image(integer, integer, text); Type: ACL; Schema: ngscl_schema; Owner: postgres
--

REVOKE ALL ON FUNCTION export_prep_note_image(in_library_prep_note_uid integer, in_image_number integer, in_temp_file text) FROM PUBLIC;
REVOKE ALL ON FUNCTION export_prep_note_image(in_library_prep_note_uid integer, in_image_number integer, in_temp_file text) FROM postgres;
GRANT ALL ON FUNCTION export_prep_note_image(in_library_prep_note_uid integer, in_image_number integer, in_temp_file text) TO postgres;
GRANT ALL ON FUNCTION export_prep_note_image(in_library_prep_note_uid integer, in_image_number integer, in_temp_file text) TO dac_grants;


--
-- Name: import_prep_note_image(integer, integer, text); Type: ACL; Schema: ngscl_schema; Owner: postgres
--

REVOKE ALL ON FUNCTION import_prep_note_image(in_library_prep_note_uid integer, in_image_number integer, in_uploadfile text) FROM PUBLIC;
REVOKE ALL ON FUNCTION import_prep_note_image(in_library_prep_note_uid integer, in_image_number integer, in_uploadfile text) FROM postgres;
GRANT ALL ON FUNCTION import_prep_note_image(in_library_prep_note_uid integer, in_image_number integer, in_uploadfile text) TO postgres;
GRANT ALL ON FUNCTION import_prep_note_image(in_library_prep_note_uid integer, in_image_number integer, in_uploadfile text) TO dac_grants;


--
-- Name: archive; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON TABLE archive FROM PUBLIC;
REVOKE ALL ON TABLE archive FROM ngscl_schema;
GRANT ALL ON TABLE archive TO ngscl_schema;
GRANT ALL ON TABLE archive TO dac_grants;


--
-- Name: archive_archive_uid_seq; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON SEQUENCE archive_archive_uid_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE archive_archive_uid_seq FROM ngscl_schema;
GRANT ALL ON SEQUENCE archive_archive_uid_seq TO ngscl_schema;
GRANT USAGE,UPDATE ON SEQUENCE archive_archive_uid_seq TO dac_grants;


--
-- Name: contact; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON TABLE contact FROM PUBLIC;
REVOKE ALL ON TABLE contact FROM ngscl_schema;
GRANT ALL ON TABLE contact TO ngscl_schema;
GRANT ALL ON TABLE contact TO dac_grants;


--
-- Name: contact_contact_uid_seq; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON SEQUENCE contact_contact_uid_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE contact_contact_uid_seq FROM ngscl_schema;
GRANT ALL ON SEQUENCE contact_contact_uid_seq TO ngscl_schema;
GRANT USAGE,UPDATE ON SEQUENCE contact_contact_uid_seq TO dac_grants;


--
-- Name: deleted_project_log; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON TABLE deleted_project_log FROM PUBLIC;
REVOKE ALL ON TABLE deleted_project_log FROM ngscl_schema;
GRANT ALL ON TABLE deleted_project_log TO ngscl_schema;
GRANT INSERT ON TABLE deleted_project_log TO dac_grants;
GRANT INSERT ON TABLE deleted_project_log TO pi_user;


--
-- Name: deleted_project_log_deleted_project_log_uid_seq; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON SEQUENCE deleted_project_log_deleted_project_log_uid_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE deleted_project_log_deleted_project_log_uid_seq FROM ngscl_schema;
GRANT ALL ON SEQUENCE deleted_project_log_deleted_project_log_uid_seq TO ngscl_schema;
GRANT USAGE,UPDATE ON SEQUENCE deleted_project_log_deleted_project_log_uid_seq TO dac_grants;
GRANT USAGE,UPDATE ON SEQUENCE deleted_project_log_deleted_project_log_uid_seq TO pi_user;


--
-- Name: deleted_project_log_run_lane; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON TABLE deleted_project_log_run_lane FROM PUBLIC;
REVOKE ALL ON TABLE deleted_project_log_run_lane FROM ngscl_schema;
GRANT ALL ON TABLE deleted_project_log_run_lane TO ngscl_schema;
GRANT INSERT ON TABLE deleted_project_log_run_lane TO dac_grants;
GRANT INSERT ON TABLE deleted_project_log_run_lane TO pi_user;

--
-- Name: deleted_project_log_run_lane_deleted_project_log_run_lane_u_seq; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON SEQUENCE deleted_project_log_run_lane_deleted_project_log_run_lane_u_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE deleted_project_log_run_lane_deleted_project_log_run_lane_u_seq FROM ngscl_schema;
GRANT ALL ON SEQUENCE deleted_project_log_run_lane_deleted_project_log_run_lane_u_seq TO ngscl_schema;
GRANT USAGE,UPDATE ON SEQUENCE deleted_project_log_run_lane_deleted_project_log_run_lane_u_seq TO dac_grants;
GRANT USAGE,UPDATE ON SEQUENCE deleted_project_log_run_lane_deleted_project_log_run_lane_u_seq TO pi_user;


--
-- Name: library_prep_note; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON TABLE library_prep_note FROM PUBLIC;
REVOKE ALL ON TABLE library_prep_note FROM ngscl_schema;
GRANT ALL ON TABLE library_prep_note TO ngscl_schema;
GRANT ALL ON TABLE library_prep_note TO dac_grants;


--
-- Name: library_prep_note_image; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON TABLE library_prep_note_image FROM PUBLIC;
REVOKE ALL ON TABLE library_prep_note_image FROM ngscl_schema;
GRANT ALL ON TABLE library_prep_note_image TO ngscl_schema;
GRANT ALL ON TABLE library_prep_note_image TO dac_grants;


--
-- Name: library_prep_note_image_library_prep_note_image_uid_seq; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON SEQUENCE library_prep_note_image_library_prep_note_image_uid_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE library_prep_note_image_library_prep_note_image_uid_seq FROM ngscl_schema;
GRANT ALL ON SEQUENCE library_prep_note_image_library_prep_note_image_uid_seq TO ngscl_schema;
GRANT USAGE,UPDATE ON SEQUENCE library_prep_note_image_library_prep_note_image_uid_seq TO dac_grants;


--
-- Name: library_prep_note_library_prep_note_uid_seq; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON SEQUENCE library_prep_note_library_prep_note_uid_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE library_prep_note_library_prep_note_uid_seq FROM ngscl_schema;
GRANT ALL ON SEQUENCE library_prep_note_library_prep_note_uid_seq TO ngscl_schema;
GRANT USAGE,UPDATE ON SEQUENCE library_prep_note_library_prep_note_uid_seq TO dac_grants;


--
-- Name: library_prep_note_sample; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON TABLE library_prep_note_sample FROM PUBLIC;
REVOKE ALL ON TABLE library_prep_note_sample FROM ngscl_schema;
GRANT ALL ON TABLE library_prep_note_sample TO ngscl_schema;
GRANT ALL ON TABLE library_prep_note_sample TO dac_grants;


--
-- Name: library_prep_note_sample_library_prep_note_sample_uid_seq; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON SEQUENCE library_prep_note_sample_library_prep_note_sample_uid_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE library_prep_note_sample_library_prep_note_sample_uid_seq FROM ngscl_schema;
GRANT ALL ON SEQUENCE library_prep_note_sample_library_prep_note_sample_uid_seq TO ngscl_schema;
GRANT USAGE,UPDATE ON SEQUENCE library_prep_note_sample_library_prep_note_sample_uid_seq TO dac_grants;


--
-- Name: ngscl_role; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON TABLE ngscl_role FROM PUBLIC;
REVOKE ALL ON TABLE ngscl_role FROM ngscl_schema;
GRANT ALL ON TABLE ngscl_role TO ngscl_schema;
GRANT ALL ON TABLE ngscl_role TO dac_grants;


--
-- Name: notification_address; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON TABLE notification_address FROM PUBLIC;
REVOKE ALL ON TABLE notification_address FROM ngscl_schema;
GRANT ALL ON TABLE notification_address TO ngscl_schema;
GRANT ALL ON TABLE notification_address TO dac_grants;


--
-- Name: notification_address_notification_address_uid_seq; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON SEQUENCE notification_address_notification_address_uid_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE notification_address_notification_address_uid_seq FROM ngscl_schema;
GRANT ALL ON SEQUENCE notification_address_notification_address_uid_seq TO ngscl_schema;
GRANT USAGE,UPDATE ON SEQUENCE notification_address_notification_address_uid_seq TO dac_grants;


--
-- Name: pi16_contact; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON TABLE pi16_contact FROM PUBLIC;
REVOKE ALL ON TABLE pi16_contact FROM ngscl_schema;
GRANT ALL ON TABLE pi16_contact TO ngscl_schema;

--
-- Name: primary_investigator; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON TABLE primary_investigator FROM PUBLIC;
REVOKE ALL ON TABLE primary_investigator FROM ngscl_schema;
GRANT ALL ON TABLE primary_investigator TO ngscl_schema;
GRANT ALL ON TABLE primary_investigator TO dac_grants;


--
-- Name: pi16_primary_investigator; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON TABLE pi16_primary_investigator FROM PUBLIC;
REVOKE ALL ON TABLE pi16_primary_investigator FROM ngscl_schema;
GRANT ALL ON TABLE pi16_primary_investigator TO ngscl_schema;

--
-- Name: project; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON TABLE project FROM PUBLIC;
REVOKE ALL ON TABLE project FROM ngscl_schema;
GRANT ALL ON TABLE project TO ngscl_schema;
GRANT ALL ON TABLE project TO dac_grants;


--
-- Name: pi16_project; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON TABLE pi16_project FROM PUBLIC;
REVOKE ALL ON TABLE pi16_project FROM ngscl_schema;
GRANT ALL ON TABLE pi16_project TO ngscl_schema;


--
-- Name: project_log; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON TABLE project_log FROM PUBLIC;
REVOKE ALL ON TABLE project_log FROM ngscl_schema;
GRANT ALL ON TABLE project_log TO ngscl_schema;
GRANT ALL ON TABLE project_log TO dac_grants;


--
-- Name: pi16_project_log; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON TABLE pi16_project_log FROM PUBLIC;
REVOKE ALL ON TABLE pi16_project_log FROM ngscl_schema;
GRANT ALL ON TABLE pi16_project_log TO ngscl_schema;


--
-- Name: project_log_run_lane; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON TABLE project_log_run_lane FROM PUBLIC;
REVOKE ALL ON TABLE project_log_run_lane FROM ngscl_schema;
GRANT ALL ON TABLE project_log_run_lane TO ngscl_schema;
GRANT ALL ON TABLE project_log_run_lane TO dac_grants;


--
-- Name: pi16_project_log_run_lane; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON TABLE pi16_project_log_run_lane FROM PUBLIC;
REVOKE ALL ON TABLE pi16_project_log_run_lane FROM ngscl_schema;
GRANT ALL ON TABLE pi16_project_log_run_lane TO ngscl_schema;


--
-- Name: run; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON TABLE run FROM PUBLIC;
REVOKE ALL ON TABLE run FROM ngscl_schema;
GRANT ALL ON TABLE run TO ngscl_schema;
GRANT ALL ON TABLE run TO dac_grants;


--
-- Name: run_lane; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON TABLE run_lane FROM PUBLIC;
REVOKE ALL ON TABLE run_lane FROM ngscl_schema;
GRANT ALL ON TABLE run_lane TO ngscl_schema;
GRANT ALL ON TABLE run_lane TO dac_grants;


--
-- Name: run_lane_sample; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON TABLE run_lane_sample FROM PUBLIC;
REVOKE ALL ON TABLE run_lane_sample FROM ngscl_schema;
GRANT ALL ON TABLE run_lane_sample TO ngscl_schema;
GRANT ALL ON TABLE run_lane_sample TO dac_grants;


--
-- Name: sample; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON TABLE sample FROM PUBLIC;
REVOKE ALL ON TABLE sample FROM ngscl_schema;
GRANT ALL ON TABLE sample TO ngscl_schema;
GRANT ALL ON TABLE sample TO dac_grants;


--
-- Name: pi16_run; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON TABLE pi16_run FROM PUBLIC;
REVOKE ALL ON TABLE pi16_run FROM ngscl_schema;
GRANT ALL ON TABLE pi16_run TO ngscl_schema;


--
-- Name: pi16_run_lane; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON TABLE pi16_run_lane FROM PUBLIC;
REVOKE ALL ON TABLE pi16_run_lane FROM ngscl_schema;
GRANT ALL ON TABLE pi16_run_lane TO ngscl_schema;


--
-- Name: pi16_run_lane_sample; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON TABLE pi16_run_lane_sample FROM PUBLIC;
REVOKE ALL ON TABLE pi16_run_lane_sample FROM ngscl_schema;
GRANT ALL ON TABLE pi16_run_lane_sample TO ngscl_schema;

--
-- Name: pi16_sample; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON TABLE pi16_sample FROM PUBLIC;
REVOKE ALL ON TABLE pi16_sample FROM ngscl_schema;
GRANT ALL ON TABLE pi16_sample TO ngscl_schema;

--
-- Name: pi_ngscl_role; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON TABLE pi_ngscl_role FROM PUBLIC;
REVOKE ALL ON TABLE pi_ngscl_role FROM ngscl_schema;
GRANT ALL ON TABLE pi_ngscl_role TO ngscl_schema;
GRANT SELECT ON TABLE pi_ngscl_role TO dac_grants;

--
-- Name: primary_investigator_primary_investigator_uid_seq; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON SEQUENCE primary_investigator_primary_investigator_uid_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE primary_investigator_primary_investigator_uid_seq FROM ngscl_schema;
GRANT ALL ON SEQUENCE primary_investigator_primary_investigator_uid_seq TO ngscl_schema;
GRANT USAGE,UPDATE ON SEQUENCE primary_investigator_primary_investigator_uid_seq TO dac_grants;


--
-- Name: project_log_project_log_uid_seq; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON SEQUENCE project_log_project_log_uid_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE project_log_project_log_uid_seq FROM ngscl_schema;
GRANT ALL ON SEQUENCE project_log_project_log_uid_seq TO ngscl_schema;
GRANT USAGE,UPDATE ON SEQUENCE project_log_project_log_uid_seq TO dac_grants;
GRANT USAGE,UPDATE ON SEQUENCE project_log_project_log_uid_seq TO pi_user;


--
-- Name: project_log_run_lane_project_log_run_lane_uid_seq; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON SEQUENCE project_log_run_lane_project_log_run_lane_uid_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE project_log_run_lane_project_log_run_lane_uid_seq FROM ngscl_schema;
GRANT ALL ON SEQUENCE project_log_run_lane_project_log_run_lane_uid_seq TO ngscl_schema;
GRANT USAGE,UPDATE ON SEQUENCE project_log_run_lane_project_log_run_lane_uid_seq TO dac_grants;
GRANT USAGE,UPDATE ON SEQUENCE project_log_run_lane_project_log_run_lane_uid_seq TO pi_user;


--
-- Name: project_project_uid_seq; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON SEQUENCE project_project_uid_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE project_project_uid_seq FROM ngscl_schema;
GRANT ALL ON SEQUENCE project_project_uid_seq TO ngscl_schema;
GRANT USAGE,UPDATE ON SEQUENCE project_project_uid_seq TO dac_grants;


--
-- Name: ref_archive_name; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON TABLE ref_archive_name FROM PUBLIC;
REVOKE ALL ON TABLE ref_archive_name FROM ngscl_schema;
GRANT ALL ON TABLE ref_archive_name TO ngscl_schema;
GRANT ALL ON TABLE ref_archive_name TO dac_grants;


--
-- Name: ref_archive_name_ref_archive_name_uid_seq; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON SEQUENCE ref_archive_name_ref_archive_name_uid_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE ref_archive_name_ref_archive_name_uid_seq FROM ngscl_schema;
GRANT ALL ON SEQUENCE ref_archive_name_ref_archive_name_uid_seq TO ngscl_schema;
GRANT USAGE,UPDATE ON SEQUENCE ref_archive_name_ref_archive_name_uid_seq TO dac_grants;


--
-- Name: ref_barcode; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON TABLE ref_barcode FROM PUBLIC;
REVOKE ALL ON TABLE ref_barcode FROM ngscl_schema;
GRANT ALL ON TABLE ref_barcode TO ngscl_schema;
GRANT ALL ON TABLE ref_barcode TO dac_grants;
GRANT SELECT ON TABLE ref_barcode TO pi_user;


--
-- Name: ref_barcode_ref_barcode_uid_seq; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON SEQUENCE ref_barcode_ref_barcode_uid_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE ref_barcode_ref_barcode_uid_seq FROM ngscl_schema;
GRANT ALL ON SEQUENCE ref_barcode_ref_barcode_uid_seq TO ngscl_schema;
GRANT USAGE,UPDATE ON SEQUENCE ref_barcode_ref_barcode_uid_seq TO dac_grants;


--
-- Name: ref_prep_type; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON TABLE ref_prep_type FROM PUBLIC;
REVOKE ALL ON TABLE ref_prep_type FROM ngscl_schema;
GRANT ALL ON TABLE ref_prep_type TO ngscl_schema;
GRANT ALL ON TABLE ref_prep_type TO dac_grants;
GRANT SELECT ON TABLE ref_prep_type TO pi_user;


--
-- Name: ref_prep_type_ref_prep_type_uid_seq; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON SEQUENCE ref_prep_type_ref_prep_type_uid_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE ref_prep_type_ref_prep_type_uid_seq FROM ngscl_schema;
GRANT ALL ON SEQUENCE ref_prep_type_ref_prep_type_uid_seq TO ngscl_schema;
GRANT USAGE,UPDATE ON SEQUENCE ref_prep_type_ref_prep_type_uid_seq TO dac_grants;


--
-- Name: ref_run_type; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON TABLE ref_run_type FROM PUBLIC;
REVOKE ALL ON TABLE ref_run_type FROM ngscl_schema;
GRANT ALL ON TABLE ref_run_type TO ngscl_schema;
GRANT ALL ON TABLE ref_run_type TO dac_grants;
GRANT ALL ON TABLE ref_run_type TO pi_user;


--
-- Name: ref_run_type_ref_run_type_uid_seq; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON SEQUENCE ref_run_type_ref_run_type_uid_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE ref_run_type_ref_run_type_uid_seq FROM ngscl_schema;
GRANT ALL ON SEQUENCE ref_run_type_ref_run_type_uid_seq TO ngscl_schema;
GRANT USAGE,UPDATE ON SEQUENCE ref_run_type_ref_run_type_uid_seq TO dac_grants;


--
-- Name: run_lane_run_lane_uid_seq; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON SEQUENCE run_lane_run_lane_uid_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE run_lane_run_lane_uid_seq FROM ngscl_schema;
GRANT ALL ON SEQUENCE run_lane_run_lane_uid_seq TO ngscl_schema;
GRANT USAGE,UPDATE ON SEQUENCE run_lane_run_lane_uid_seq TO dac_grants;


--
-- Name: run_lane_sample_run_lane_sample_uid_seq; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON SEQUENCE run_lane_sample_run_lane_sample_uid_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE run_lane_sample_run_lane_sample_uid_seq FROM ngscl_schema;
GRANT ALL ON SEQUENCE run_lane_sample_run_lane_sample_uid_seq TO ngscl_schema;
GRANT USAGE,UPDATE ON SEQUENCE run_lane_sample_run_lane_sample_uid_seq TO dac_grants;


--
-- Name: run_run_uid_seq; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON SEQUENCE run_run_uid_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE run_run_uid_seq FROM ngscl_schema;
GRANT ALL ON SEQUENCE run_run_uid_seq TO ngscl_schema;
GRANT USAGE,UPDATE ON SEQUENCE run_run_uid_seq TO dac_grants;


--
-- Name: sample_sample_uid_seq; Type: ACL; Schema: ngscl_schema; Owner: ngscl_schema
--

REVOKE ALL ON SEQUENCE sample_sample_uid_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE sample_sample_uid_seq FROM ngscl_schema;
GRANT ALL ON SEQUENCE sample_sample_uid_seq TO ngscl_schema;
GRANT USAGE,UPDATE ON SEQUENCE sample_sample_uid_seq TO dac_grants;


--
-- PostgreSQL database dump complete
--

BEGIN;
SELECT setval ('ngscl_schema.ref_archive_name_ref_archive_name_uid_seq', 1, FALSE);
-- ref_archive_name
INSERT INTO ngscl_schema.ref_archive_name
 (archive_name)
VALUES
 ('Sample');
INSERT INTO ngscl_schema.ref_archive_name
 (archive_name)
VALUES
 ('Library');
INSERT INTO ngscl_schema.ref_archive_name
 (archive_name)
VALUES
 ('Pre capture library');
SELECT setval ('ngscl_schema.ref_run_type_ref_run_type_uid_seq', 1, FALSE);
-- ref_run_type
INSERT INTO ngscl_schema.ref_run_type
 (run_type)
VALUES
 ('HiSeq');
INSERT INTO ngscl_schema.ref_run_type
 (run_type)
VALUES
 ('MiSeq');
COMMIT;
