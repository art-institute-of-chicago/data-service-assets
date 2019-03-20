# coding: utf-8
class Artwork < BaseModel

  def initialize
    super
    self.fq = 'hasModel:Work'
  end

  def transform( data, ret )

    # Explicitly enforced in case we start using prefLabel in model_base.rb [#2423]
    ret[:title] = data.get(:title)
    ret[:alt_titles] = data.get(:altTitle, false)

    ret[:main_id] = data.get(:mainRefNumber) # unusual for this model

    ret[:date_display] = data.get(:dateDisplay)
    ret[:date_start] = Integer( data.get(:earliestYear) ) rescue nil # can be derived from dates?
    ret[:date_end] = Integer( data.get(:latestYear) ) rescue nil # can be derived from dates?

    ret[:creator_id] = str2int( data.get(:artist_uid) )
    ret[:creator_display] = data.get(:creatorDisplay)

    # copyrightRepresentative, copyrightRepresentative_uid, copyrightRepresentative_uri
    ret[:copyright_representative_ids] = str2int( data.get(:copyrightRepresentative_uid, false) )

    # hasDocument_uid, hasDocument_uri, hasDocument
    ret[:document_ids] = Uri2Guid( data.get(:hasDocument_uri, false) )

    # TODO: Rename this field to image_id
    # TODO: Rename this field to pref_image_id?
    ret[:image_guid] = Uri2Guid( data.get(:hasPreferredRepresentation_uri) )

    # TODO: Rename this field to alt_image_id?
    ret[:alt_image_guids] = Uri2Guid( data.get(:hasRepresentation_uri, false) )

    # Remove the prefImage from the altImages array
    if ret[:alt_image_guids] && ret[:image_guid]
      ret[:alt_image_guids].delete( ret[:image_guid] )
    end

    # Remove documents from altImages
    # "When [interpretive resources] were migrated, they were added as both representations and documentation"
    if ret[:alt_image_guids] && ret[:document_ids]
      ret[:alt_image_guids] = ret[:alt_image_guids] - ret[:document_ids]
    end

    # TODO: Remove this once the DA has been modified to work w/ gallery_ud
    ret[:location] = data.get(:galleryLocation)

    ret[:gallery_id] = str2int( data.get(:gallery_uid) )

    ret[:description] = data.get(:description)

    ret[:dimensions] = data.get(:dimensionsDisplay)

    ret[:medium] = data.get(:mediumDisplay)

    ret[:credit_line] = data.get(:creditLine)

    ret[:copyright] = data.get(:copyrightNotice, false)

    ret[:inscriptions] = data.get(:inscriptions)

    ret[:publications] = data.get(:publicationHistory)
    ret[:exhibitions] = data.get(:exhibitionHistory)
    ret[:provenance] = data.get(:provenanceText)

    # This is always an array of strings
    ret[:committees] = data.get(:objectCommittee, false)

    # Parse committees to get fiscal year of acquisition
    if ret[:committees]
      fy = nil;
      ret[:committees].each { |c|
        comm_re = /([a-zA-Z\(\) ]+?)\s\(Acquisition\)\s\((\d{2})\/\d{2}\/(\d{4})\)/
        m = comm_re.match(c)
        unless m
          next
        end

        if m[1] != "Board of Trustees" &&
           m[1] != "Year End Gifts" &&
           m[1] != "Executive Committee" &&
           m[1] != "Executive Committee (Poll)" &&
           m[1] != "Director's Discretion"
          next
        end

        comm_fy = m[3].to_i
        if m[2].to_i >= 7
          comm_fy = comm_fy + 1
        end

        if fy == nil || comm_fy > fy
          fy = comm_fy
        end
      }
    end

    ret[:fiscal_year] = fy

    ret[:internal_department_id] = str2int( data.get(:department_uid) )

    # TODO: Change this to publishCategory_citiUid once that's available
    ret[:category_ids] = data.get(:published_category_i, false)

    # prefTerm, prefTerm_uri, prefTerm_uid
    ret[:pref_term_ids] = str2int( data.get(:prefTerm_uid, false) )

    # altTerm, altTerm_uri, altTerm_uid
    ret[:alt_term_ids] = str2int( data.get(:altTerm_uid, false) )

    # All the `:artwork_*_ids` fields below point at "pivot" objects
    # We need to import these pivot objects, then use them to relate artworks to the "actual" linked object
    # Most of these "pivot" objects have extra fields elaborating on the relationship

    # Note that the "pivot" objects don't know what artworks link to them
    # That info has to be gotten from the artwork side!

    # objectAgent, objectAgent_uri, objectAgent_uid
    if data.get(:objectAgentsJSON, false)
      json = data.json(:objectAgentsJSON)

      ret[:artwork_agent_ids] = json.map {|x| x["pkey"]}
      ret[:artwork_agents] = ArtworkAgent.new.transform!(json)
    else
      ret[:artwork_agent_ids] = nil
      ret[:artwork_agents] = nil
    end

    # objectCatalogRaisonnesJSON, objectCatalogRaisonne, objectCatalogRaisonne_uri, objectCatalogRaisonne_uid
    if data.get(:objectCatalogRaisonnesJSON, false)
      json = data.json(:objectCatalogRaisonnesJSON)
      json = json.each{|x|
        x["parent_lake_guid"] = ret[:lake_guid]
        x["parent_lake_uri"] = ret[:lake_uri]
      }

      ret[:artwork_catalogue_ids] = json.map {|x| x["pkey"]}
      ret[:artwork_catalogues] = ArtworkCatalogue.new.transform!(json)
    else
      ret[:artwork_catalogue_ids] = nil
      ret[:artwork_catalogues] = nil
    end

    # objectDate, objectDate_uri, objectDate_uid
    if data.get(:objectDatesJSON, false)
      json = data.json(:objectDatesJSON)

      ret[:artwork_date_ids] = json.map {|x| x["pkey"]}
      ret[:artwork_dates] = ArtworkDate.new.transform!(json)
    else
      ret[:artwork_date_ids] = nil
      ret[:artwork_dates] = nil
    end

    # TODO: Watch Redmine ticket #2425
    # objectPlace, objectPlace_uri, objectPlace_uid
    if data.get(:objectPlacesJSON, false)
      json = data.json(:objectPlacesJSON)

      ret[:artwork_place_ids] = json.map {|x| x["pkey"]}
      ret[:artwork_places] = ArtworkPlace.new.transform!(json)
    else
      ret[:artwork_place_ids] = nil
      ret[:artwork_places] = nil
    end

    # TODO: Watch Redmine ticket #2431
    # objectTypes

    # This produces an Artwork's CITI UID
    # constituentPart_uid, constituentPart_uri, constituentPart
    ret[:part_ids] = str2int( data.get(:constituentPart_uid, false) )

    # This produces an Artwork's CITI UID
    # compositeWhole_uid, compositeWhole_uri, compositeWhole
    ret[:set_ids] = str2int( data.get(:compositeWhole_uid, false) )

    # This produces an id of a `Group` (`hasModel:Set`)
    # Sets are M2O relationships to a List in CITI
    # isMemberOfSet, isMemberOfSet_uid, isMemberOfSet_uri
    # TODO: Determine if this is many-to-one i/o many-to-many
    # TODO: Rename this to `set` after migration to Laravel
    ret[:group_id] = str2int( data.get(:isMemberOfSet_uid) )

    ret[:is_public_domain] = data.get(:type, false).include? 'http://definitions.artic.edu/ontology/1.0/PCRightsPublicDomain' rescue nil

    ret[:is_zoomable] = (data.get(:type, false).include? 'http://definitions.artic.edu/ontology/1.0/PCRightsWebEdu') || ret[:is_public_domain] || !ret[:copyright].nil? || false rescue nil

    if ret[:copyright]
      ret[:max_zoom_window_size] = 1280
    elsif ret[:is_zoomable]
      ret[:max_zoom_window_size] = -1
    else
      ret[:max_zoom_window_size] = 843
    end

    # TODO: Determine if this was obsolesced by artwork_place_ids
    ret[:place_of_origin] = data.get(:placeOfOrigin)

    ret[:publishing_verification_level] = data.get(:publicationVerificationLevel, false)

    ret[:collection_status] = data.get(:collectionStatus, false)

    ret

  end
end
