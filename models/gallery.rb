class Gallery < BaseModel

  def initialize
    super
    self.fq = 'hasModel:Place'
    # Using galleryNumber excludes 20 places, such as Terzo Piano, Modern Wing Entrance, etc.
    # https://lakesolridxweb.artic.edu/solr/lpm_prod/select?wt=json&q=hasModel%3APlace+AND+galleryFloor%3A%5B*+TO+*%5D%20AND%20-galleryNumber:[*%20TO%20*]
    # self.fq << ' AND galleryNumber:[* TO *]'
    # self.fq << ' AND galleryFloor:[* TO *]'
    # Only 46 of 287 Galleries in Solr have the below condition:
    # self.fq << ' AND locationType:"AIC Gallery"'
    self.fq << ' AND type:"http://definitions.artic.edu/ontology/1.0/WebMobilePublished"'
  end

  # TODO: Abstract boolean into lake_unwrapper.rb (?)
  # isClosed contains some irregularities that prevent it from abstraction
  # https://lakesolridxweb.artic.edu/solr/lpm/select?wt=json&facet.field=isClosed&facet.limit=-1&rows=0
  def isClosed( data )

    # default to expectations...
    return true if data == "<Closed>"

    return false if data == "<NOT Closed>"
    return false

    # historic responses, for reference:
    return false if data == nil
    return true if data == "True"
    return false if data == "False"
    return false if data == "<NOT Closed>"

  end

  def transform( data, ret )

    ret[:closed] = isClosed( data.get(:isClosed) )

    # Some galleryNumbers are NOT numbers, e.g. 297A
    ret[:number] = data.get(:galleryNumber)

    # Some galleryFloors are NOT numbers, e.g. LL
    # https://lakesolridxweb.artic.edu/solr/lpm_prod/select?wt=json&facet.field=galleryFloor&facet.limit=-1&rows=0
    ret[:floor] = data.get(:galleryFloor)

    ret[:latitude] = data.get(:latitude, false)
    ret[:longitude] = data.get(:longitude, false)

    ret[:type] = data.get(:locationType)

    # I don't want to pass names. Waiting until we get GUIDs.
    # ret[:category] = data.get(:publishCategory)

    ret

  end
end